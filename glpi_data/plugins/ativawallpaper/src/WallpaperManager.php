<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use RuntimeException;
use Throwable;

final class WallpaperManager
{
    private const STYLES = ['fill', 'fit', 'stretch', 'center', 'tile', 'span'];

    public function current(): ?array
    {
        global $DB;
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativawallpaper_wallpapers',
            'WHERE' => ['is_current' => 1],
            'LIMIT' => 1,
        ]);
        $row = $iterator->current();
        return is_array($row) ? $row : null;
    }

    public function findById(int $id): ?array
    {
        global $DB;
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativawallpaper_wallpapers',
            'WHERE' => ['id' => $id],
            'LIMIT' => 1,
        ]);
        $row = $iterator->current();
        return is_array($row) ? $row : null;
    }

    public function findByIdentifier(string $identifier): ?array
    {
        global $DB;
        $where = ctype_digit($identifier)
            ? ['OR' => ['id' => (int) $identifier, 'version' => $identifier]]
            : ['version' => $identifier];
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativawallpaper_wallpapers',
            'WHERE' => $where,
            'LIMIT' => 1,
        ]);
        $row = $iterator->current();
        return is_array($row) ? $row : null;
    }

    public function history(int $start = 0, int $limit = 50): array
    {
        global $DB;
        $rows = [];
        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_ativawallpaper_wallpapers',
            'ORDER' => ['published_at DESC', 'id DESC'],
            'START' => max(0, $start),
            'LIMIT' => max(1, min(100, $limit)),
        ]);
        foreach ($iterator as $row) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function count(): int
    {
        return countElementsInTable('glpi_plugin_ativawallpaper_wallpapers');
    }

    public function publishUploaded(array $upload, string $style, bool $lockChange, int $userId): array
    {
        global $DB;

        if (!in_array($style, self::STYLES, true)) {
            throw new RuntimeException('Modo de ajuste invalido.');
        }
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->uploadError((int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE)));
        }
        $tmpName = (string) ($upload['tmp_name'] ?? '');
        if (!is_uploaded_file($tmpName)) {
            throw new RuntimeException('O arquivo nao foi recebido por upload HTTP valido.');
        }

        $metadata = ImageValidator::validate(
            $tmpName,
            (string) ($upload['name'] ?? ''),
            ConfigService::getInt('max_upload_mb') * 1024 * 1024,
            ConfigService::getInt('max_image_dimension')
        );

        $random = bin2hex(random_bytes(24));
        $internal = $random . '.' . $metadata['extension'];
        $thumbnail = bin2hex(random_bytes(24)) . '.jpg';
        $staging = Storage::temporaryPath('.tmp');
        $thumbnailStaging = Storage::temporaryPath('.tmp');
        $destination = Storage::wallpaperPath($internal);
        $thumbnailDestination = Storage::thumbnailPath($thumbnail);

        if (!move_uploaded_file($tmpName, $staging)) {
            throw new RuntimeException('Falha ao mover o upload para armazenamento privado.');
        }

        $transactionStarted = false;
        $lockAcquired = false;
        $databaseCommitted = false;
        try {
            $sha256 = hash_file('sha256', $staging);
            if ($sha256 === false) {
                throw new RuntimeException('Falha ao calcular SHA-256.');
            }
            $this->createThumbnail($staging, $metadata['mime_type'], $thumbnailStaging);

            if (!rename($staging, $destination) || !rename($thumbnailStaging, $thumbnailDestination)) {
                throw new RuntimeException('Falha na publicacao atomica dos arquivos.');
            }
            @chmod($destination, 0640);
            @chmod($thumbnailDestination, 0640);

            $lockAcquired = (bool) $DB->getLock('ativawallpaper_version');
            if (!$lockAcquired) {
                throw new RuntimeException('Outra publicacao esta em andamento. Tente novamente.');
            }
            $latest = null;
            $today = date('Ymd');
            $iterator = $DB->request([
                'SELECT' => ['version'],
                'FROM'   => 'glpi_plugin_ativawallpaper_wallpapers',
                'WHERE'  => ['version' => ['LIKE', $today . '-%']],
                'ORDER'  => ['version DESC'],
                'LIMIT'  => 1,
            ]);
            if (is_array($iterator->current())) {
                $latest = (string) $iterator->current()['version'];
            }
            $version = Version::next($latest, $today);
            $now = date('Y-m-d H:i:s');

            $DB->beginTransaction();
            $transactionStarted = true;
            $DB->update('glpi_plugin_ativawallpaper_wallpapers', ['is_current' => 0], ['is_current' => 1]);
            $DB->insert('glpi_plugin_ativawallpaper_wallpapers', [
                'version'            => $version,
                'filename'           => $internal,
                'thumbnail_filename' => $thumbnail,
                'original_filename'  => $this->safeOriginalName((string) ($upload['name'] ?? 'wallpaper')),
                'mime_type'          => $metadata['mime_type'],
                'width'              => $metadata['width'],
                'height'             => $metadata['height'],
                'filesize'           => $metadata['filesize'],
                'sha256'             => $sha256,
                'style'              => $style,
                'lock_change'        => $lockChange ? 1 : 0,
                'is_current'         => 1,
                'created_at'         => $now,
                'created_by'         => $userId,
                'published_at'       => $now,
            ]);
            $id = (int) $DB->insertId();
            ConfigService::rotatePublicationRevision();
            Audit::record('publish', 'wallpaper', $id, null, [
                'version' => $version,
                'sha256'  => $sha256,
                'style'   => $style,
            ]);
            $DB->commit();
            $transactionStarted = false;
            $databaseCommitted = true;
            $DB->releaseLock('ativawallpaper_version');
            $lockAcquired = false;

            $wallpaper = $this->findById($id);
            if ($wallpaper === null) {
                throw new RuntimeException('Wallpaper publicado, mas nao foi possivel rele-lo.');
            }
            return $wallpaper;
        } catch (Throwable $exception) {
            if ($transactionStarted) {
                $DB->rollBack();
            }
            if ($lockAcquired) {
                $DB->releaseLock('ativawallpaper_version');
            }
            @unlink($staging);
            @unlink($thumbnailStaging);
            if (!$databaseCommitted) {
                @unlink($destination);
                @unlink($thumbnailDestination);
            }
            throw $exception;
        }
    }

    public function makeCurrent(int $id): array
    {
        global $DB;
        $target = $this->findById($id);
        if ($target === null) {
            throw new RuntimeException('Wallpaper nao encontrado.');
        }
        $previous = $this->current();

        $DB->beginTransaction();
        try {
            $DB->update('glpi_plugin_ativawallpaper_wallpapers', ['is_current' => 0], ['is_current' => 1]);
            $DB->update(
                'glpi_plugin_ativawallpaper_wallpapers',
                ['is_current' => 1],
                ['id' => $id]
            );
            ConfigService::rotatePublicationRevision();
            Audit::record('rollback', 'wallpaper', $id, $previous['version'] ?? null, $target['version']);
            $DB->commit();
        } catch (Throwable $exception) {
            $DB->rollBack();
            throw $exception;
        }

        return $this->findById($id) ?? $target;
    }

    private function createThumbnail(string $source, string $mime, string $destination): void
    {
        $image = $mime === 'image/jpeg' ? @imagecreatefromjpeg($source) : @imagecreatefrompng($source);
        if ($image === false) {
            throw new RuntimeException('Falha ao decodificar imagem para thumbnail.');
        }
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $width = min(640, $sourceWidth);
        $height = max(1, (int) round($sourceHeight * ($width / $sourceWidth)));
        $thumbnail = imagecreatetruecolor($width, $height);
        if ($thumbnail === false) {
            imagedestroy($image);
            throw new RuntimeException('Falha ao criar thumbnail.');
        }
        $white = imagecolorallocate($thumbnail, 255, 255, 255);
        imagefill($thumbnail, 0, 0, $white);
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        $written = imagejpeg($thumbnail, $destination, 85);
        imagedestroy($thumbnail);
        imagedestroy($image);
        if (!$written) {
            throw new RuntimeException('Falha ao gravar thumbnail.');
        }
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        return Security::cleanText($name, 255) ?: 'wallpaper';
    }

    private function uploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O upload excede o limite permitido.',
            UPLOAD_ERR_PARTIAL => 'O upload foi interrompido antes de terminar.',
            UPLOAD_ERR_NO_FILE => 'Nenhuma imagem foi selecionada.',
            default => 'Falha ao receber o upload.',
        };
    }
}
