<?php

declare(strict_types=1);

/**
 * This file is part of the demo_cms package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Lexik\Bundle\TranslationBundle\Manager\TransUnitManagerInterface;
use Lexik\Bundle\TranslationBundle\Storage\StorageInterface;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationCRUDController extends CRUDController
{
    /**
     * TranslationCRUDController constructor.
     */
    public function __construct(
        CmsEventDispatcher $dispatcher,
        PageCacheInterface $pageCache,
        private readonly TransUnitManagerInterface $transUnitManager,
        private readonly StorageInterface $storage,
        private readonly TranslatorInterface $translator
    ) {
        parent::__construct($dispatcher, $pageCache);
    }

    /**
     * Edit action.
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     * @throws AccessDeniedException If access is not granted
     */
    public function editAction(Request $request): Response
    {
        $existingObject = $this->assertObjectExists($request, true);
        \assert(null !== $existingObject);

        $this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);
        $objectId = $this->admin->getNormalizedIdentifier($existingObject);

        if (!$request->isMethod('POST')) {
            return $this->redirect($this->admin->generateUrl('list'));
        }

        $transUnit = $this->storage->getTransUnitById($objectId);
        if (!$transUnit) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $objectId));
        }

        if (false === $this->admin->isGranted('EDIT', $transUnit)) {
            return $this->renderJson(
                ['message' => 'access denied'],
                403
            );
        }

        $this->admin->setSubject($transUnit);

        $parameters = $request->request;

        $locale = $parameters->get('locale');
        $content = $parameters->get('value');

        if (!$locale) {
            return $this->renderJson(
                ['message' => 'locale missing'],
                422
            );
        }

        if ($parameters->get('pk')) {
            $translation = $this->transUnitManager->updateTranslation(
                $transUnit,
                $locale,
                $content,
                true
            );
        } else {
            $translation = $this->transUnitManager->addTranslation(
                $transUnit,
                $locale,
                $content,
                null,
                true
            );
        }

        if ($request->query->get('clear_cache')) {
            $this->container->get('translator')->removeLocalesCacheFiles(
                [$locale]
            );
        }

        return $this->renderJson(
            [
                'key' => $transUnit->getKey(),
                'domain' => $transUnit->getDomain(),
                'pk' => $translation->getId(),
                'locale' => $translation->getLocale(),
                'value' => $translation->getContent(),
            ]
        );
    }

    public function createTransUnitAction(Request $request
    ): RedirectResponse|Response {
        $parameters = $request->request;
        if (!$request->isMethod('POST')) {
            return $this->renderJson(
                ['message' => 'method not allowed'],
                403
            );
        }
        $admin = $this->admin;
        if (false === $admin->isGranted('EDIT')) {
            return $this->renderJson(
                ['message' => 'access denied'],
                403
            );
        }
        $keyName = $parameters->get('key');
        $domainName = $parameters->get('domain');
        if (!$keyName || !$domainName) {
            return $this->renderJson(
                ['message' => 'missing key or domain'],
                422
            );
        }

        $transUnit = $this->transUnitManager->create(
            $keyName,
            $domainName,
            true
        );

        return $this->editAction($transUnit->getId());
    }

    /**
     * @return RedirectResponse
     */
    public function clearCacheAction(Request $request)
    {
        $languages = $this->getManagedLocales();

        $this->translator->removeLocalesCacheFiles(
            $languages
        );

        if ($this->pageCache->isActive()) {
            $this->pageCache->clean();
        }

        $session = $request->getSession();
        $session->getFlashBag()->set(
            'sonata_flash_success',
            $this->translate('translations.cache_removed', [], 'NetworkingInitCmsBundle')
        );

        return $this->redirect($request->headers->get('referer'));
    }

    public function batchActionDownload(ProxyQueryInterface $query
    ): RedirectResponse|StreamedResponse {
        $flashType = 'success';

        $dumper = new Dumper(4);

        $token = $this->admin->getRequest()->get('downloadToken');

        $cookie = Cookie::create(
            'downloadToken',
            $token,
            0,
            '/',
            null,
            false,
            false
        );

        $response = new StreamedResponse(
            function () use ($query, &$flashType, $dumper) {
                try {
                    /*
                     * @var TransUnit
                     */
                    foreach (
                        $query->getQuery()->getResult() as $pos => $transUnit
                    ) {
                        $chunkPrefix = $transUnit->getDomain().'__'
                            .$transUnit->getKey().'__'.$transUnit->getId().'__';
                        $chunk = [];
                        foreach ($transUnit->getTranslations() as $translation) {
                            $chunk[$chunkPrefix.$translation->getLocale()]
                                = $translation->getContent();
                        }
                        echo $dumper->dump($chunk, 2);
                        flush();
                        ob_flush();
                    }
                } catch (\Throwable $e) {
                    $flashType = 'error';
                    flush();
                }
            }
        );

        $this->addFlash(
            'sonata_flash_'.$flashType,
            'translations.flash_batch_download_'.$flashType
        );

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
        return $this->getParameter('lexik_translation.managed_locales');
    }
}
