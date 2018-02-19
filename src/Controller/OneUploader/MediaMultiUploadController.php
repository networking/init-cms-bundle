<?php
namespace Networking\InitCmsBundle\Controller\OneUploader;

use Doctrine\Common\Collections\ArrayCollection;
use Networking\InitCmsBundle\Entity\Media;
use Networking\InitCmsBundle\Exception\DuplicateMediaException;
use Oneup\UploaderBundle\Controller\AbstractController;
use Oneup\UploaderBundle\Uploader\Response\FineUploaderResponse;
use Oneup\UploaderBundle\Uploader\Response\ResponseInterface;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Gaufrette\Util;

/**
 * This file is part of the init-cms-sandbox  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class MediaMultiUploadController extends AbstractController
{


    public function upload()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $response = new FineUploaderResponse();
        $files = $this->getFiles($request->files);

        foreach ((array)$files as $file) {
            try {
                $this->handleUpload($file, $response, $request);
            } catch (UploadException $e) {
                $response->setSuccess(false);
                $response->setError($e->getMessage());
                if($e instanceof DuplicateMediaException){
                    $response->offsetSet('duplicate', true);
                }
                $this->errorHandler->addException($response, $e);
            }
        }

        $status = $response->getError() ? 500 : 200;

        return $this->createSupportedJsonResponse($response->assemble(), $status);
    }

    /**
     * @param $file
     * @param ResponseInterface $response
     * @param Request $request
     */
    protected function handleUpload($file, ResponseInterface $response, Request $request)
    {

        /** @var $mediaAdmin \Sonata\MediaBundle\Admin\ORM\MediaAdmin */
        $mediaAdmin = $this->container->get('sonata.media.admin.media');

        $mediaAdmin->setRequest($request);

        /** @var Media $media */
        $media = $mediaAdmin->getNewInstance();
        $media->setEnabled(true);
        $media->setName($file->getClientOriginalName());
        $media->setBinaryContent($file);
        $tags = explode(',', $request->get('tags'));
        $tagCollection = new ArrayCollection();
        foreach($tags as $tagId){
            $tag = $this->container->get('doctrine')->getRepository('NetworkingInitCmsBundle:Tag')->find($tagId);

            if($tag){
                $tagCollection->add($tag);
            }
        }

        $media->setTags($tagCollection);
        $provider = $mediaAdmin->getPool()->getProvider($media->getProviderName());


        $provider->transform($media);

        $validator = $this->container->get('validator');

        $errors = $validator->validate($media);

        $errorMessages = [];
        if($errors->count() > 0){
            $duplicate = false;
            foreach ($errors as $error){
                $errorMessages[] = $error->getMessage();

                if($error->getMessage() == 'File is duplicate'){
                    $duplicate = true;
                }
            }

            if($duplicate){
                $originalMedia = $mediaAdmin->checkForDuplicate($media);
                $path = $mediaAdmin->generateObjectUrl('edit', $originalMedia);
                $response->offsetSet('url', $path);
                $response->offsetSet('id', $originalMedia->getId());

                throw new DuplicateMediaException('File is duplicate');
            }

            throw new UploadException(join(', ', $errorMessages));
        }

        try{
            $mediaAdmin->create($media);
            $path = $mediaAdmin->generateObjectUrl('edit', $media);
            $response->offsetSet('url', $path);
            $response->offsetSet('id', $media->getId());
        }catch (\Exception $e){
            throw new UploadException($e->getMessage());
        }

    }
}