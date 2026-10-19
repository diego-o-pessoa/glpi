<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativaprint;

use RuntimeException;

/**
 * Cliente SNMP v1/v2c minimo, em PHP puro (UDP + BER).
 *
 * A imagem do GLPI nao traz a extensao php-snmp e ela se perderia a cada
 * recriacao do container. Para ler impressoras bastam GET e GETNEXT (walk),
 * entao o protocolo e montado aqui, sem dependencia nenhuma.
 *
 * Somente leitura: nunca envia SET.
 */
final class SnmpClient
{
    private const TYPE_INTEGER      = 0x02;
    private const TYPE_OCTET_STRING = 0x04;
    private const TYPE_NULL         = 0x05;
    private const TYPE_OID          = 0x06;
    private const TYPE_SEQUENCE     = 0x30;
    private const TYPE_IPADDRESS    = 0x40;
    private const TYPE_COUNTER32    = 0x41;
    private const TYPE_GAUGE32      = 0x42;
    private const TYPE_TIMETICKS    = 0x43;
    private const TYPE_COUNTER64    = 0x46;
    private const TYPE_NO_SUCH_OBJECT   = 0x80;
    private const TYPE_NO_SUCH_INSTANCE = 0x81;
    private const TYPE_END_OF_MIB       = 0x82;

    private const PDU_GET      = 0xA0;
    private const PDU_GETNEXT  = 0xA1;
    private const PDU_RESPONSE = 0xA2;

    /** Limite de linhas num walk (protege contra agentes com tabela em loop). */
    private const MAX_WALK = 256;

    private string $host;
    private string $community;
    private int $version;
    private float $timeout;
    private int $retries;

    /** @var resource|null */
    private $socket = null;

    public function __construct(string $host, string $community = 'public', string $version = '2c', float $timeout = 1.0, int $retries = 1)
    {
        $this->host      = $host;
        $this->community = $community;
        $this->version   = $version === '1' ? 0 : 1;
        $this->timeout   = max(0.2, $timeout);
        $this->retries   = max(0, $retries);
    }

    public function __destruct()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    /**
     * GET de varios OIDs de uma vez. OIDs que o agente nao conhece ficam de fora.
     *
     * @param list<string> $oids
     * @return array<string, int|string> oid => valor
     */
    public function get(array $oids): array
    {
        try {
            $result = [];
            foreach ($this->request(self::PDU_GET, $oids) as [$oid, $type, $value]) {
                if (!in_array($type, [self::TYPE_NO_SUCH_OBJECT, self::TYPE_NO_SUCH_INSTANCE, self::TYPE_END_OF_MIB, self::TYPE_NULL], true)) {
                    $result[$oid] = $value;
                }
            }
            if ($result !== []) {
                return $result;
            }
        } catch (RuntimeException $e) {
            // Se falhou o pacote agrupado, tenta consultar OIDs individualmente
        }

        $result = [];
        foreach ($oids as $oid) {
            try {
                foreach ($this->request(self::PDU_GET, [$oid]) as [$resOid, $type, $value]) {
                    if (!in_array($type, [self::TYPE_NO_SUCH_OBJECT, self::TYPE_NO_SUCH_INSTANCE, self::TYPE_END_OF_MIB, self::TYPE_NULL], true)) {
                        $result[$resOid] = $value;
                    }
                }
            } catch (RuntimeException) {
                // silencia OID individual não suportado
            }
        }
        return $result;
    }

    /**
     * Percorre uma subarvore com GETNEXT.
     *
     * @return array<string, int|string> oid completo => valor
     */
    public function walk(string $base): array
    {
        $base = self::normalizeOid($base);
        $current = $base;
        $result = [];
        for ($i = 0; $i < self::MAX_WALK; $i++) {
            $rows = $this->request(self::PDU_GETNEXT, [$current]);
            if ($rows === []) {
                break;
            }
            [$oid, $type, $value] = $rows[0];
            if ($type === self::TYPE_END_OF_MIB || !str_starts_with($oid . '.', $base . '.') || $oid === $current) {
                break;
            }
            $result[$oid] = $value;
            $current = $oid;
        }
        return $result;
    }

    // ------------------------------------------------------------------ rede

    /**
     * @param list<string> $oids
     * @return list<array{0: string, 1: int, 2: int|string}>
     */
    private function request(int $pduType, array $oids): array
    {
        $requestId = random_int(1, 0x7FFFFFFF);
        $varbinds = '';
        foreach ($oids as $oid) {
            $varbinds .= self::tlv(self::TYPE_SEQUENCE, self::encodeOid($oid) . self::tlv(self::TYPE_NULL, ''));
        }
        $pdu = self::tlv(
            $pduType,
            self::encodeInteger($requestId) . self::encodeInteger(0) . self::encodeInteger(0) . self::tlv(self::TYPE_SEQUENCE, $varbinds)
        );
        $packet = self::tlv(
            self::TYPE_SEQUENCE,
            self::encodeInteger($this->version) . self::tlv(self::TYPE_OCTET_STRING, $this->community) . $pdu
        );

        $socket = $this->socket();
        for ($attempt = 0; $attempt <= $this->retries; $attempt++) {
            if (@fwrite($socket, $packet) === false) {
                throw new RuntimeException('Falha ao enviar a consulta SNMP.');
            }
            $deadline = microtime(true) + $this->timeout;
            while (($left = $deadline - microtime(true)) > 0) {
                $read = [$socket];
                $write = $except = null;
                $sec = (int) floor($left);
                $usec = (int) (($left - $sec) * 1_000_000);
                if (@stream_select($read, $write, $except, $sec, $usec) < 1) {
                    break;
                }
                $data = @fread($socket, 65535);
                if ($data === false || $data === '') {
                    // ICMP "porta inacessivel": o host respondeu que nao ha SNMP.
                    throw new RuntimeException('O equipamento recusou a consulta SNMP.');
                }
                $parsed = $this->parseResponse($data, $requestId);
                if ($parsed !== null) {
                    return $parsed;
                }
                // Resposta atrasada de outra consulta: continua esperando.
            }
        }
        throw new RuntimeException('Sem resposta SNMP (tempo esgotado).');
    }

    /** @return resource */
    private function socket()
    {
        if (is_resource($this->socket)) {
            return $this->socket;
        }
        $host = filter_var($this->host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? '[' . $this->host . ']' : $this->host;
        $socket = @stream_socket_client('udp://' . $host . ':161', $errno, $error, $this->timeout);
        if (!is_resource($socket)) {
            throw new RuntimeException('Não foi possível abrir a conexão SNMP: ' . ($error ?: 'erro ' . $errno));
        }
        stream_set_blocking($socket, false);
        $this->socket = $socket;
        return $socket;
    }

    /**
     * @return list<array{0: string, 1: int, 2: int|string}>|null null = nao e a resposta esperada
     */
    private function parseResponse(string $data, int $requestId): ?array
    {
        try {
            $offset = 0;
            [$type, $message] = self::readTlv($data, $offset);
            if ($type !== self::TYPE_SEQUENCE) {
                return null;
            }
            $pos = 0;
            self::readTlv($message, $pos);              // versao
            self::readTlv($message, $pos);              // community
            [$pduType, $pdu] = self::readTlv($message, $pos);
            if ($pduType !== self::PDU_RESPONSE) {
                return null;
            }
            $p = 0;
            [, $rid] = self::readTlv($pdu, $p);
            if (self::decodeInteger($rid) !== $requestId) {
                return null;
            }
            [, $errorStatus] = self::readTlv($pdu, $p);
            self::readTlv($pdu, $p);                    // error-index
            [, $list] = self::readTlv($pdu, $p);
            $status = self::decodeInteger($errorStatus);
            if ($status === 2) {
                // noSuchName (SNMP v1): nenhum dos OIDs existe neste agente.
                return [];
            }
            if ($status !== 0) {
                throw new RuntimeException('O equipamento devolveu erro SNMP ' . $status . '.');
            }

            $rows = [];
            $v = 0;
            $length = strlen($list);
            while ($v < $length) {
                [, $bind] = self::readTlv($list, $v);
                $b = 0;
                [, $oidRaw] = self::readTlv($bind, $b);
                [$valueType, $valueRaw] = self::readTlv($bind, $b);
                $rows[] = [self::decodeOid($oidRaw), $valueType, self::decodeValue($valueType, $valueRaw)];
            }
            return $rows;
        } catch (\LengthException $exception) {
            return null;
        }
    }

    // ------------------------------------------------------------------ BER

    private static function tlv(int $type, string $value): string
    {
        return chr($type) . self::encodeLength(strlen($value)) . $value;
    }

    private static function encodeLength(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }
        $bytes = '';
        while ($length > 0) {
            $bytes = chr($length & 0xFF) . $bytes;
            $length >>= 8;
        }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    private static function encodeInteger(int $value): string
    {
        $bytes = '';
        do {
            $bytes = chr($value & 0xFF) . $bytes;
            $value >>= 8;
        } while ($value !== 0 && $value !== -1);
        // Bit de sinal: positivo com o bit alto ligado ganha um zero na frente.
        $first = ord($bytes[0]);
        if ($value === 0 && ($first & 0x80)) {
            $bytes = "\x00" . $bytes;
        } elseif ($value === -1 && !($first & 0x80)) {
            $bytes = "\xFF" . $bytes;
        }
        return self::tlv(self::TYPE_INTEGER, $bytes);
    }

    private static function encodeOid(string $oid): string
    {
        $parts = array_map('intval', explode('.', self::normalizeOid($oid)));
        if (count($parts) < 2) {
            throw new RuntimeException('OID inválido: ' . $oid);
        }
        $body = chr(40 * $parts[0] + $parts[1]);
        for ($i = 2, $n = count($parts); $i < $n; $i++) {
            $sub = $parts[$i];
            $chunk = chr($sub & 0x7F);
            $sub >>= 7;
            while ($sub > 0) {
                $chunk = chr(0x80 | ($sub & 0x7F)) . $chunk;
                $sub >>= 7;
            }
            $body .= $chunk;
        }
        return self::tlv(self::TYPE_OID, $body);
    }

    /** @return array{0: int, 1: string} */
    private static function readTlv(string $data, int &$offset): array
    {
        $length = strlen($data);
        if ($offset + 2 > $length) {
            throw new \LengthException('Pacote SNMP truncado.');
        }
        $type = ord($data[$offset++]);
        $size = ord($data[$offset++]);
        if ($size & 0x80) {
            $count = $size & 0x7F;
            if ($count < 1 || $count > 4 || $offset + $count > $length) {
                throw new \LengthException('Tamanho BER inválido.');
            }
            $size = 0;
            for ($i = 0; $i < $count; $i++) {
                $size = ($size << 8) | ord($data[$offset++]);
            }
        }
        if ($offset + $size > $length) {
            throw new \LengthException('Pacote SNMP truncado.');
        }
        $value = substr($data, $offset, $size);
        $offset += $size;
        return [$type, $value];
    }

    private static function decodeInteger(string $raw): int
    {
        if ($raw === '') {
            return 0;
        }
        $value = ord($raw[0]) & 0x80 ? -1 : 0;
        for ($i = 0, $n = strlen($raw); $i < $n; $i++) {
            $value = ($value << 8) | ord($raw[$i]);
        }
        return $value;
    }

    private static function decodeUnsigned(string $raw): int
    {
        $value = 0;
        for ($i = 0, $n = strlen($raw); $i < $n; $i++) {
            $value = ($value << 8) | ord($raw[$i]);
        }
        return $value;
    }

    private static function decodeOid(string $raw): string
    {
        if ($raw === '') {
            return '';
        }
        $first = ord($raw[0]);
        $parts = [intdiv($first, 40), $first % 40];
        if ($first >= 80) {
            $parts = [2, $first - 80];
        }
        $sub = 0;
        for ($i = 1, $n = strlen($raw); $i < $n; $i++) {
            $byte = ord($raw[$i]);
            $sub = ($sub << 7) | ($byte & 0x7F);
            if (!($byte & 0x80)) {
                $parts[] = $sub;
                $sub = 0;
            }
        }
        return implode('.', $parts);
    }

    /** @return int|string */
    private static function decodeValue(int $type, string $raw)
    {
        switch ($type) {
            case self::TYPE_INTEGER:
                return self::decodeInteger($raw);
            case self::TYPE_COUNTER32:
            case self::TYPE_GAUGE32:
            case self::TYPE_TIMETICKS:
            case self::TYPE_COUNTER64:
                return self::decodeUnsigned($raw);
            case self::TYPE_OID:
                return self::decodeOid($raw);
            case self::TYPE_IPADDRESS:
                return strlen($raw) === 4 ? implode('.', array_map('ord', str_split($raw))) : '';
            default:
                return $raw;
        }
    }

    private static function normalizeOid(string $oid): string
    {
        return trim($oid, ". \t");
    }
}

