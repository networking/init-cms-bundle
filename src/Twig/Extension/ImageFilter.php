<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Twig\Extension;

use League\Flysystem\Filesystem;
use Twig\TwigFilter;

class ImageFilter extends \Twig\Extension\AbstractExtension
{
    public function __construct(private readonly Filesystem $flysystem, private readonly Filesystem $cacheFlysystem)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('webp_image', $this->webpImage(...)),
        ];
    }

    public function webpImage(string $imagePath, ?int $width = null, ?int $height = null, $compression = 80): string
    {
        $mimeType = $this->flysystem->mimeType(urldecode($imagePath));
        if ('image/svg' === $mimeType || 'image/svg+xml' === $mimeType) {
            return $imagePath;
        }

        $suffix = '.webp';

        if (null !== $height && null !== $width) {
            $suffix = '-'.$width.'x'.$height.$suffix;
        }

        if (null !== $width && null === $height) {
            $suffix = '-'.$width.'w'.$suffix;
        }

        if (null !== $height && null === $width) {
            $suffix = '-'.$height.'h'.$suffix;
        }

        $newImagePath = str_replace(['.jpeg', '.jpg', '.png', '.gif'], $suffix, $imagePath);

        if ($this->cacheFlysystem->has($newImagePath)) {
            return '/media/cache'.$newImagePath;
        }

        $parts = explode('/', $newImagePath);

        $dir = str_replace(end($parts), '', $newImagePath);

        if (!$this->cacheFlysystem->directoryExists($dir)) {
            try {
                $this->cacheFlysystem->createDirectory($dir, ['visibility' => 'public']);
            } catch (\Exception) {
                return $imagePath;
            }
        }

        try {
            if (class_exists(\Imagick::class)) {
                $image = $this->createWithImagick($imagePath, $width, $height, $compression);
            } else {
                $image = $this->createWithGD($imagePath, $width, $height);
            }

            $this->cacheFlysystem->write($newImagePath, $image, ['visibility' => 'public']);
        } catch (\Exception $e) {
            return $imagePath;
        }

        return '/media/cache'.$newImagePath;
    }

    public function createWithGD($imagePath, $width, $height): string|false
    {
        /** @var \GdImage $gd */
        $gd = imagecreatefromstring($this->flysystem->read($imagePath));
        imagepalettetotruecolor($gd);

        ob_start();
        if ($width && $height) {
            $gd = imagescale($gd, $width, $height);
        }
        imagewebp($gd, null, IMG_WEBP_LOSSLESS);
        $gd = ob_get_contents();
        ob_end_clean();

        return $gd;
    }

    public function createWithImagick($imagePath, $width, $height, $compression): string|false
    {
        $im = new \Imagick();
        $im->readImageFile($this->flysystem->readStream($imagePath));
        if ($width && $height) {
            $im->resizeImage($width, $height, \Imagick::FILTER_UNDEFINED, 1);
        }
        $im->setImageFormat('webp');
        $im->setOption('webp:method', '6');
        $im->setImageCompressionQuality($compression);

        return $im->getImageBlob();
    }
}
