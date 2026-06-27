<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Constants\Upload;
use App\Exceptions\HttpException;

final class FileUploadHelper
{
    private static string $baseDir = '';

    public static function upload(array $file, string $directory): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new HttpException(self::uploadErrorMessage($file['error']), 422);
        }

        if ($file['size'] > Upload::MAX_SIZE_BYTES) {
            throw new HttpException('File size exceeds the 5MB limit.', 422);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, Upload::ALLOWED_MIME_TYPES, true)) {
            throw new HttpException('Invalid file type. Only JPEG, PNG, and WebP are allowed.', 422);
        }

        $ext      = self::mimeToExtension($mimeType);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir  = public_path('uploads/' . trim($directory, '/') . '/');
        $destPath = $destDir . $filename;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $moved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $destPath)
            : rename($file['tmp_name'], $destPath);

        if (!$moved) {
            throw new HttpException('Failed to save uploaded file.', 500);
        }

        return 'uploads/' . $directory . '/' . $filename;
    }

    public static function delete(?string $relativePath): void
    {
        if ($relativePath === null) {
            return;
        }

        $fullPath = public_path($relativePath);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    private static function mimeToExtension(string $mime): string
    {
        return match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'bin',
        };
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeds size limit.',
            UPLOAD_ERR_PARTIAL                        => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE                        => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR                     => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE                     => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION                      => 'File upload stopped by extension.',
            default                                   => 'Unknown upload error.',
        };
    }
}
