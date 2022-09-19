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

use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaController extends AbstractController
{
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
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->get('sonata.media.pool')->getDownloadStrategy($media)->isGranted($media, $request)) {
            throw new AccessDeniedException();
        }
        $provider = $this->getProvider($media);

        return new RedirectResponse($provider->generatePublicUrl($media, $format));
    }
}
