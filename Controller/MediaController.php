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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaController extends BaseMediaController
{

    /**
     * output image direct to browser, retrieve from cache if activated
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

        /** @var \Networking\InitCmsBundle\Lib\PhpCache $phpCache */
        $phpCache = $this->get('networking_init_cms.lib.php_cache');
        if ($phpCache->isActive()) {

            if ($phpCache->get(sprintf('image_%s_updated_at', $id)) != $media->getUpdatedAt()) {
                $phpCache->delete('image_' . $id);
            }

            if (!$response = $phpCache->get('image_' . $media->getId())) {
                $provider = $this->getProvider($media);


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
                    'Content-Disposition' => sprintf('inline; filename="%s"', $media->getMetadataValue('filename'))
                ), array());

                $response = new Response($content, 200, $headers);


                $response->setPublic();
                $response->setMaxAge(604800);
                $response->setLastModified($media->getUpdatedAt());
                $response->getEtag(md5(sprintf('image_%s_updated_at', $id)));

                $phpCache->set('image_' . $media->getId(), $response, null);
                $phpCache->set(sprintf('image_%s_updated_at', $media->getId()), $media->getUpdatedAt(), null);
            } else {
                $phpCache->touch('image_' . $media->getId(), null);
                $phpCache->touch(sprintf('image_%s_updated_at', $media->getId()), null);
            }
        } else {
            $provider = $this->getProvider($media);


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
                'Content-Disposition' => sprintf('inline; filename="%s"', $media->getMetadataValue('filename'))
            ), array());

            $response = new Response($content, 200, $headers);


            $response->setPublic();
            $response->setMaxAge(604800);
            $response->setLastModified($media->getUpdatedAt());
            $response->getEtag(md5(sprintf('image_%s_updated_at', $id)));
        }

        return $response;

    }
}