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

use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Dumper;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationCRUDController extends CRUDController
{
    /**
     * @var pageCacheInterface 
     */
    private $pageCache;

    /**
     * TranslationCRUDController constructor.
     * @param PageCacheInterface $pageCache
     */
    public function __construct(PageCacheInterface $pageCache)
    {
        $this->pageCache = $pageCache;
    }

    /**
     * Edit action
     *
     *
     * @param int|string|null $id
     * @return Response|RedirectResponse
     * @throws NotFoundHttpException If the object does not exist
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     * @throws AccessDeniedException If access is not granted
     */
    public function editAction($id = null, Request $request = null)
    {
        if (!$request) {
            $request = $this->getRequest();
        }
        if (!$request->isMethod('POST')) {
            return $this->redirect($this->admin->generateUrl('list'));
        }

        /* @var $transUnit \Lexik\Bundle\TranslationBundle\Model\TransUnit */
        $transUnit = $this->get('lexik_translation.translation_storage')->getTransUnitById($id);
        if (!$transUnit) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $transUnit)) {
            return $this->renderJson(
                array(
                    'message' => 'access denied',
                ),
                403
            );
        }

        $this->admin->setSubject($transUnit);

        /* @var $transUnitManager \Lexik\Bundle\TranslationBundle\Manager\TransUnitManager */
        $transUnitManager = $this->get('lexik_translation.trans_unit.manager');

        $parameters = $this->getRequest()->request;

        $locale = $parameters->get('locale');
        $content = $parameters->get('value');

        if (!$locale) {
            return $this->renderJson(
                array(
                    'message' => 'locale missing',
                ),
                422
            );
        }

        /* @var $translation \Lexik\Bundle\TranslationBundle\Model\Translation */
        if ($parameters->get('pk')) {
            $translation = $transUnitManager->updateTranslation($transUnit, $locale, $content, true);
        } else {
            $translation = $transUnitManager->addTranslation($transUnit, $locale, $content, null, true);
        }

        if ($request->query->get('clear_cache')) {
            $this->get('translator')->removeLocalesCacheFiles(array($locale));
        }

        return $this->renderJson(
            array(
                'key' => $transUnit->getKey(),
                'domain' => $transUnit->getDomain(),
                'pk' => $translation->getId(),
                'locale' => $translation->getLocale(),
                'value' => $translation->getContent(),
            )
        );
    }



    /**
     * @return RedirectResponse|Response
     */
    public function createTransUnitAction()
    {
        $request = $this->getRequest();
        $parameters = $this->getRequest()->request;
        if (!$request->isMethod('POST')) {
            return $this->renderJson(
                array(
                    'message' => 'method not allowed',
                ),
                403
            );
        }
        $admin = $this->admin;
        if (false === $admin->isGranted('EDIT')) {
            return $this->renderJson(
                array(
                    'message' => 'access denied',
                ),
                403
            );
        }
        $keyName = $parameters->get('key');
        $domainName = $parameters->get('domain');
        if (!$keyName || !$domainName) {
            return $this->renderJson(
                array(
                    'message' => 'missing key or domain',
                ),
                422
            );
        }

        /* @var $transUnitManager \Lexik\Bundle\TranslationBundle\Manager\TransUnitManager */
        $transUnitManager = $this->get('lexik_translation.trans_unit.manager');
        $transUnit = $transUnitManager->create($keyName, $domainName, true);

        return $this->editAction($transUnit->getId());
    }

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

        /** @var \Networking\InitCmsBundle\Lib\pageCacheInterface $pageCache */
        if ($this->pageCache->isActive()) {
            $this->pageCache->clean();
        }

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session');
        $session->getFlashBag()->set('sonata_flash_success', 'translations.cache_removed');

        return $this->redirect($this->admin->generateUrl('list'));
    }

    /**
     * @param ProxyQueryInterface $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|StreamedResponse
     */
    public function batchActionDownload(ProxyQueryInterface $query)
    {
        $flashType = 'success';

        $dumper = new Dumper(4);

        $token = $this->getRequest()->get('downloadToken');

        $cookie = Cookie::create('downloadToken', $token, 0, '/', null, false, false);

        $response = new StreamedResponse(
            function () use ($query, &$flashType, $dumper) {
                try {
                    /*
                     * @var TransUnit
                     */
                    foreach ($query->getQuery()->getResult() as $pos => $transUnit) {
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
            'translations.yaml'
        );
        $response->headers->set('Content-Disposition', $contentDisposition);

        $response->headers->setCookie($cookie);

        return $response;
    }

    protected function getManagedLocales()
    {
        return $this->container->getParameter('lexik_translation.managed_locales');
    }
}
