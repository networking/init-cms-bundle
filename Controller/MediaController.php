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

use Sonata\MediaBundle\Controller\MediaController as BaseMediaController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\Date;

class MediaController extends BaseMediaController
{

    /**
     * output image direct to browser
     *
     * @param Request $request
     * @param $id
     * @param string $format
     * @return Response
     */
    public function viewImageAction(Request $request, $id, $format = 'reference')
    {
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->get('sonata.media.pool')->getDownloadSecurity($media)->isGranted($media, $request)) {
            throw new AccessDeniedException();
        }

        $provider = $this->getProvider($media);

        $headers = array_merge(array(
            'Content-Type' => $media->getContentType(),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => $media->getSize(),
            'Content-Disposition' => sprintf('inline; filename="%s"', $media->getMetadataValue('filename'))
        ), array());


        if ($format == 'reference') {
            $file = $provider->getReferenceFile($media);
        } else {
            $file = $provider->getFilesystem()->get($provider->generatePrivateUrl($media, $format));
        }

        $response = new Response($file->getContent(), 200, $headers);

        $response->setPublic();
        $response->setMaxAge(604800);
        $response->setLastModified($media->getUpdatedAt());
        $response->getEtag(md5(sprintf('image_%s_updated_at', $id)));


        return $response;

    }

} 