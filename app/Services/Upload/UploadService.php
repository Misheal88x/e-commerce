<?php

declare(strict_types=1);

namespace App\Services\Upload;

use App\Exceptions\HttpException;

class UploadService
{
    public function uploadImage(array $file, string $directory): string
    {
        $this->validateFile($file);

        $finfo     = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType  = $finfo->file($file['tmp_name']);
        $extension = $this->extensionFromMime($mimeType);
        $filename  = bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadDir = storage_path("uploads/{$directory}/");

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            throw new HttpException('File upload failed.', 500);
        }

        return "uploads/{$directory}/{$filename}";
    }

    private function validateFile(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new HttpException('File upload error: ' . $this->uploadErrorMessage($file['error']), 422);
        }

        $maxSize = config('upload')['max_size_bytes'];
        if ($file['size'] > $maxSize) {
            throw new HttpException('File size exceeds ' . ($maxSize / 1048576) . 'MB limit.', 422);
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, config('upload')['allowed_mimes'], true)) {
            throw new HttpException('Only JPEG, PNG, and WebP images are allowed.', 422);
        }
    }

    private function extensionFromMime(string $mime): string
    {
        return match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => throw new HttpException('Unsupported file type.', 422),
        };
    }

    private function uploadErrorMessage(int $code): string
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds size limit.',
            UPLOAD_ERR_NO_FILE  => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            default => "Upload error code: {$code}",
        };
    }
}
