<?php
/**
 * This file is part of the demo_cms package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Ibrows\SonataTranslationBundle\Controller\TranslationCRUDController as IbrowsTranslationCRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Yaml\Dumper;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationCRUDController extends IbrowsTranslationCRUDController
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function clearCacheAction()
    {
        $languages = $this->container->getParameter('networking_init_cms.page.languages');
        $localeChoices = [$this->getUser()->getLocale()];
        foreach ($languages as $language) {
            $localeChoices[] = $language['locale'];
        }

        $this->get('translator')->removeLocalesCacheFiles($localeChoices);

        /** @var \Networking\InitCmsBundle\Lib\PhpCacheInterface $phpCache */
        $phpCache = $this->get('networking_init_cms.lib.php_cache');
        if ($phpCache->isActive()) {
            $phpCache->clean();
        }

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session');
        $session->getFlashBag()->set('sonata_flash_success', 'translations.cache_removed');

        return $this->redirect($this->admin->generateUrl('list'));
    }

    /**
     * @param ProxyQueryInterface $queryProxy
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|StreamedResponse
     */
    public function batchActionDownload(ProxyQueryInterface $queryProxy)
    {
        $flashType = 'success';

        $dumper = new Dumper(4);

        $token = $this->getRequest()->get('downloadToken');

        $cookie = new Cookie('downloadToken', $token,  0,  '/',  null,  false, false);

        $response = new StreamedResponse(
            function () use ($queryProxy, &$flashType, $dumper) {
                try {
                    /**
                     * @var TransUnit
                     */
                    foreach ($queryProxy->getQuery()->getResult() as $pos => $transUnit) {
                        $chunkPrefix = $transUnit->getDomain().'__'.$transUnit->getKey().'__'.$transUnit->getId().'__';
                        $chunk = array();
                        /** @var TranslationInterface $translation */
                        foreach ($transUnit->getTranslations() as $translation) {
                            $chunk[$chunkPrefix.$translation->getLocale()] = $translation->getContent();
                        }
                        echo $dumper->dump($chunk, 2);
                        flush();
                        ob_flush();
                    }
                } catch (\PDOException $e) {
                    $flashType = 'error';
                    flush();
                } catch (DBALException $e) {
                    $flashType = 'error';
                    flush();
                }
            }
        );

        $this->addFlash('sonata_flash_'.$flashType, 'translations.flash_batch_download_'.$flashType);

        $response->headers->set('Content-Type', 'text/x-yaml');
        $response->headers->set('Cache-Control', '');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
        $contentDisposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'translations.yml'
        );
        $response->headers->set('Content-Disposition', $contentDisposition);

        $response->headers->setCookie($cookie);

        return $response;
    }
}
