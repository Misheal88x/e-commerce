<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\Upload\UploadService;

trait Uploadable
{
    protected function uploadImage(array $file, string $directory): string
    {
        return (new UploadService())->uploadImage($file, $directory);
    }
}
