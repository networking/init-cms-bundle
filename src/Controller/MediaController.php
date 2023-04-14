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
    )
    {
    }
    /**
     * output image direct to browser, retrieve from cache if activated.
     *
     * @param Request $request
     * @param $id
     * @param string $format
     *
     * @return Response
     */
    public function viewImageAction(Request $request, $id, $format = MediaProviderInterface::FORMAT_REFERENCE)
    {
        $media = $this->mediaManager->find($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->pool->getDownloadStrategy($media)->isGranted($media, $request)) {
            throw new AccessDeniedException();
        }
        $provider = $this->pool->getProvider($media->getProviderName());

        if ($format == 'reference') {
            $file = $provider->getReferenceFile($media);
        } else {
            $file = $provider->getFilesystem()->get($provider->generatePrivateUrl($media, $format));
        }


        $content = $file->getContent();
        $headers = array_merge(array(
            'Content-Type' => $media->getContentType(),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $media->getSize(),
            'Content-Disposition' => sprintf('inline; filename="%s"', $media->getMetadataValue('filename')),
        ), array());

       return new Response($content, 200, $headers);
    }
}
