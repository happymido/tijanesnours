<?php

namespace App\Shared\Domain\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorageInterface
{
    public function upload(UploadedFile $file, string $directory): string;
    public function getUrl(string $path): string;
    public function delete(string $path): void;
}
