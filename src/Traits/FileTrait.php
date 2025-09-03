<?php

namespace App\Traits;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

trait FileTrait
{
    private ?UploadedFile $imageFile = null;

    public function uploadImage(
        ?UploadedFile $imageFile,
        string $uploadDir,
        string $propertyName = 'image',
        array $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'],
        int $maxSize = 2 * 1024 * 1024
    ): ?string {
        if (null === $imageFile) {
            return $this->$propertyName ?? null;
        }

        if (!in_array($imageFile->getMimeType(), $allowedMimes, true)) {
            throw new \InvalidArgumentException('Invalid file type. Allowed types: ' . implode(', ', $allowedMimes));
        }

        if ($imageFile->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds the maximum limit of ' . ($maxSize / 1024 / 1024) . 'MB');
        }

        $fileName = uniqid('file_') . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move($uploadDir, $fileName);
            $this->$propertyName = $fileName;
        } catch (FileException $e) {
            throw new FileException('Failed to upload file: ' . $e->getMessage());
        }

        return $this->$propertyName;
    }

    public function deleteImage(string $uploadDir, string $propertyName = 'image'): bool
    {
        if (empty($this->$propertyName)) {
            return true;
        }

        $filePath = $uploadDir . '/' . $this->$propertyName;
        $filesystem = new Filesystem();

        if ($filesystem->exists($filePath)) {
            try {
                $filesystem->remove($filePath);
                $this->$propertyName = null;
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        $this->$propertyName = null;
        return true;
    }

    /**
     * Get the uploaded file object
     *
     * @return UploadedFile|null
     */
    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }
    public function setImageFile(?UploadedFile $imageFile): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }
}
