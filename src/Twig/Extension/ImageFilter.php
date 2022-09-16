<?php
namespace Networking\InitCmsBundle\Twig\Extension;

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use Twig\TwigFilter;

class ImageFilter extends \Twig\Extension\AbstractExtension{

    /**
     * @var Filesystem
     */
    private $flysystem;

    /**
     * @var Filesystem
     */
    private $cacheFlysystem;

    /**
     * @param Filesystem $flysystem
     * @param Filesystem $cacheFlysystem
     */
    public function __construct(Filesystem $flysystem, Filesystem $cacheFlysystem)
    {
        $this->flysystem = $flysystem;
        $this->cacheFlysystem = $cacheFlysystem;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('webp_image', [$this, 'webpImage'])
        ];
    }

    public function webpImage(string $imagePath, ?int $width = null, ?int $height = null, $compression = 80): string
    {

        $mimeType = $this->flysystem->getMimetype($imagePath);
        if($mimeType === 'image/svg' || $mimeType === 'image/svg+xml'){
            return $imagePath;
        }

        $newImagePath = str_replace(['.jpeg','.jpg', '.png', '.gif'], '.webp', $imagePath);

        if($this->cacheFlysystem->has($newImagePath)){
            return '/media/cache'.$newImagePath ;
        }

        $parts = explode('/', $newImagePath);

        $dir = str_replace(end($parts), '', $newImagePath);


        if(!is_dir($this->cacheFlysystem->getAdapter()->getPathPrefix().$dir)){
            $dirCreated = $this->cacheFlysystem->getAdapter()->createDir($dir, new Config(['visibility' => 'public']));
            if(!$dirCreated){
                return $imagePath;
            }
        }

        try{
            $im = new \Imagick($this->flysystem->getAdapter()->applyPathPrefix($imagePath));
            if($width && $height){
                $im->resizeImage($width, $height, \Imagick::FILTER_UNDEFINED, 1);
            }
            $im->setImageFormat( "webp" );
            $im->setOption('webp:method', '6');
            $im->setImageCompressionQuality($compression);
            $im->writeImage($this->cacheFlysystem->getAdapter()->applyPathPrefix($newImagePath));
        }catch (\Exception $e){
            return $imagePath;
        }

        return '/media/cache'.$newImagePath;
    }
}