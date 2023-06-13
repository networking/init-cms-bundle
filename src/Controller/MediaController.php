<?php
/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaController extends AbstractController
{
    public function __construct(
        private readonly MediaManagerInterface $mediaManager,
        private readonly Pool $pool
    ) {
    }

    /**
     * output image direct to browser, retrieve from cache if activated.
     *
     * @param Request $request
     * @param         $id
     * @param string  $format
     *
     * @return Response
     */
    public function viewAction(
        Request $request,
        $id,
        $name = null,
        $format = MediaProviderInterface::FORMAT_REFERENCE
    ) {
        $media = $this->mediaManager->find($id);

        if (!$media) {
            throw new NotFoundHttpException(
                sprintf('unable to find the media with the id : %s', $id)
            );
        }

        if (!$this->pool->getDownloadStrategy($media)->isGranted(
            $media,
            $request
        )
        ) {
            throw new AccessDeniedException();
        }
        $provider = $this->pool->getProvider($media->getProviderName());

        if ($format == 'reference') {
            $file = $provider->getReferenceFile($media);
        } else {
            $file = $provider->getFilesystem()->get(
                $provider->generatePrivateUrl($media, $format)
            );
        }

        $name = $name ?: $media->getName();

        return $this->getResponse($file, $media, $name, 'inline');
    }

    public function downloadAction(
        Request $request,
        $id,
        $name = null,
        $format = MediaProviderInterface::FORMAT_REFERENCE
    ) {
        $media = $this->mediaManager->find($id);

        if (!$media) {
            throw new NotFoundHttpException(
                sprintf('unable to find the media with the id : %s', $id)
            );
        }

        if (!$this->pool->getDownloadStrategy($media)->isGranted(
            $media,
            $request
        )
        ) {
            throw new AccessDeniedException();
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        if ('reference' == $format) {
            $file = $provider->getReferenceFile($media);
        } else {
            $file = $provider->getFilesystem()->get(
                $provider->generatePrivateUrl($media, $format)
            );
        }

        $name = $name ?: $media->getName();

        return $this->getResponse($file, $media, $name);

    }


    /**
     * @param mixed $file
     * @param MediaInterface $media
     * @param mixed $name
     * @param mixed $type
     *
     * @return Response
     */
    protected function getResponse(
        File $file,
        MediaInterface $media,
        mixed $name,
        $mode = 'attachment'
    ): Response {
        $content = $file->getContent();

        $type = $media->getExtension();


        $name = str_replace('.'.$type, '', $name);

        $headers = array_merge(
            [
                'Content-Type' => $media->getContentType(),
                'Accept-Ranges' => 'bytes',
                'Content-Length' => $media->getSize(),
                'Content-Disposition' => sprintf(
                    $mode.'; filename="%s.%s"',
                    $name,
                    $type
                ),
            ],
            []
        );

        $response = new Response($content, 200, $headers);

        $response->setPublic();
        $response->setLastModified($media->getUpdatedAt());
        $response->getEtag();

        return $response;
    }
}
