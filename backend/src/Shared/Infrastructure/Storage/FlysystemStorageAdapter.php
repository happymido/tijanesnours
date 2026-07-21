<?php

namespace App\Shared\Infrastructure\Storage;

use App\Shared\Domain\Storage\FileStorageInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FlysystemStorageAdapter implements FileStorageInterface
{
    public function __construct(
        private readonly FilesystemOperator $defaultStorage
    ) {}

    public function upload(UploadedFile $file, string $directory): string
    {
        $filename = sprintf('%s/%s-%s.%s', rtrim($directory, '/'), uniqid(), time(), $file->guessExtension() ?? 'bin');
        $stream = fopen($file->getPathname(), 'r');

        $this->defaultStorage->writeStream($filename, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $filename;
    }

    public function getUrl(string $path): string
    {
        return '/uploads/' . ltrim($path, '/');
    }

    public function delete(string $path): void
    {
        if ($this->defaultStorage->has($path)) {
            $this->defaultStorage->delete($path);
        }
    }
}
