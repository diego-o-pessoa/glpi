<?php

declare(strict_types=1);

namespace GlpiPlugin\Ativawallpaper;

use RuntimeException;

final class ImageValidator
{
    /** @return array{mime_type:string,width:int,height:int,filesize:int,extension:string} */
    public static function validate(string $path, string $originalName, int $maxBytes, int $maxDimension): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Arquivo de upload ausente ou ilegivel.');
        }

        $size = filesize($path);
        if ($size === false || $size < 1 || $size > $maxBytes) {
            throw new RuntimeException('Tamanho da imagem invalido ou acima do limite configurado.');
        }

        $submittedExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($submittedExtension, ['jpg', 'jpeg', 'png'], true)) {
            throw new RuntimeException('Somente arquivos JPG, JPEG e PNG sao aceitos.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new RuntimeException('O conteudo enviado nao e uma imagem JPG ou PNG valida.');
        }

        if (($mime === 'image/jpeg' && !in_array($submittedExtension, ['jpg', 'jpeg'], true))
            || ($mime === 'image/png' && $submittedExtension !== 'png')) {
            throw new RuntimeException('A extensao nao corresponde ao tipo real da imagem.');
        }

        $info = @getimagesize($path);
        if ($info === false || !isset($info[0], $info[1], $info['mime']) || $info['mime'] !== $mime) {
            throw new RuntimeException('Nao foi possivel decodificar a imagem enviada.');
        }
        $width = (int) $info[0];
        $height = (int) $info[1];
        if ($width < 1 || $height < 1 || $width > $maxDimension || $height > $maxDimension
            || ($width * $height) > 40000000) {
            throw new RuntimeException('As dimensoes da imagem sao invalidas ou excedem o limite.');
        }

        $image = $mime === 'image/jpeg' ? @imagecreatefromjpeg($path) : @imagecreatefrompng($path);
        if ($image === false) {
            throw new RuntimeException('A imagem esta corrompida ou usa codificacao nao suportada.');
        }
        imagedestroy($image);

        return [
            'mime_type' => $mime,
            'width'     => $width,
            'height'    => $height,
            'filesize'  => (int) $size,
            'extension' => $mime === 'image/jpeg' ? 'jpg' : 'png',
        ];
    }
}
