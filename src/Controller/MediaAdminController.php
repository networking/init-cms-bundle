<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use Networking\InitCmsBundle\Entity\Media;
use Networking\InitCmsBundle\Entity\Tag;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\MediaBundle\Controller\MediaAdminController as SonataMediaAdminController;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MediaAdminController
 * @package Networking\InitCmsBundle\Controller
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaAdminController extends SonataMediaAdminController
{
    /**
     * @param null $id
     * @return Response
     */
    public function showAction($id = null)
    {
        if (false === $this->admin->checkAccess('VIEW')) {
            throw new AccessDeniedException();
        }

        $media = $this->admin->getObject($id);

        if (!$media) {
            throw new NotFoundHttpException('unable to find the media with the id');
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/MediaAdmin/show.html.twig',
            [
                'media' => $media,
                'formats' => $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
                'format' => $this->getRequest()->get('format', 'reference'),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'security' => $this->get('sonata.media.pool')->getDownloadStrategy($media),
                'action' => 'view',
                'pixlr' => $this->container->has('sonata.media.extra.pixlr') ? $this->container->get(
                    'sonata.media.extra.pixlr'
                ) : false,
            ]
        );
    }

    /**
     * @param Request|null $request
     * @return Response
     */
    public function createAction(Request $request = null)
    {
        if (false === $this->admin->checkAccess('create')) {
            throw new AccessDeniedException();
        }

        $parameters = $this->admin->getPersistentParameters();


        if (!array_key_exists('provider', $parameters) || !$parameters['provider']) {
            return $this->render(
                '@NetworkingInitCms/MediaAdmin/select_provider.html.twig',
                [
                    'providers' => $this->get('sonata.media.pool')->getProvidersByContext(
                        $request->get('context', $this->get('sonata.media.pool')->getDefaultContext())
                    ),
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create'
                ]
            );
        }


        return parent::createAction($request);
    }

    /**
     * redirect the user depend on this choice
     *
     * @param object $object
     *
     * @return Response
     */
    public function redirectTo($object)
    {
        $url = false;

        if ($this->getRequest()->get('btn_update_and_list')) {
            $url = $this->admin->generateUrl('list', ['active_tab' => $this->getRequest()->get('context')]);
        }
        if ($this->getRequest()->get('btn_create_and_list')) {
            $url = $this->admin->generateUrl('list', ['active_tab' => $this->getRequest()->get('context')]);
        }


        if ($this->getRequest()->get('btn_create_and_create')) {
            $params = [];
            if ($this->admin->hasActiveSubClass()) {
                $params['subclass'] = $this->getRequest()->get('subclass');
            }
            $url = $this->admin->generateUrl('create', $params);
        }

        if (!$url) {
            $url = $this->admin->generateObjectUrl('edit', $object);
        }

        return new RedirectResponse($url);
    }

    /**
     *
     * @param mixed $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response|RedirectResponse
     */
    public function deleteAction($id)
    {
        $id = $this->getRequest()->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }
        /** @var Request $request */
        $request = $this->get('request_stack')->getCurrentRequest();
        if ($request->getMethod() == 'DELETE') {
            try {
                $this->admin->delete($object);
                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'flash_delete_success');
            } catch (ModelManagerException $e) {
                $this->get('session')->getFlashBag()->add('sonata_flash_error', 'flash_delete_error');
            }

            return new RedirectResponse($this->admin->generateUrl(
                'list',
                ['active_tab' => $this->getRequest()->get('context')]
            ));
        }

        return $this->render(
            $this->admin->getTemplate('delete'),
            [
                'object' => $object,
                'action' => 'delete'
            ]
        );
    }

    /**
     * execute a batch delete
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchActionDelete(ProxyQueryInterface $query)
    {
        if (false === $this->admin->checkAccess('DELETE')) {
            throw new AccessDeniedException();
        }


        try {
            $this->doBatchDelete($query);

            $this->addFlash('sonata_flash_success', 'flash_batch_delete_success');
        } catch (ModelManagerException $e) {
            $this->addFlash('sonata_flash_error', 'flash_batch_delete_error');
        }

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            ['filter' => $this->admin->getFilterParameters()]
        ));
    }

    public function batchActionAddTags(ProxyQueryInterface $selectedModelQuery)
    {
        $tagAdmin = $this->get('networking_init_cms.admin.tag');
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $modelManager = $tagAdmin->getModelManager();

        /** @var Tag $tag */
        $tag = $modelManager->find($tagAdmin->getClass(), $this->get('request_stack')->getCurrentRequest()->get('tags'));


        $data = [
            'result' => 'ok',
            'status' => 'warning',
            'message' => $this->admin->trans('tag_not_selected')
        ];

        if ($tag !== null) {

            $selectedModels = $selectedModelQuery->execute();

            try {
                /** @var Media $selectedModel */
                foreach ($selectedModels as $selectedModel) {
                    if(!$this->getParameter('networking_init_cms.multiple_media_tags')){
                        $selectedModel->setTags(new ArrayCollection());
                    }
                    $selectedModel->addTags($tag);
                    $this->admin->getModelManager()->update($selectedModel);
                }

                $status = 'success';
                $message = 'tag_added';

            } catch (\Exception $e) {
                $status = 'error';
                $message = 'tag_not_added';
            }

            $data = [
                'result' => 'ok',
                'status' => $status,
                'message' => $this->admin->trans($message, ['%tag%' => $tag->getPath()])];
        }



        return $this->renderJson($data);

    }

    /**
     * @param ProxyQueryInterface $queryProxy
     * @throws ModelManagerException
     */
    protected function doBatchDelete(ProxyQueryInterface $queryProxy)
    {
        $modelManager = $this->admin->getModelManager();
        $class = $this->admin->getClass();

        $queryProxy->select('DISTINCT ' . $queryProxy->getRootAlias());

        try {
            $entityManager = $modelManager->getEntityManager($class);

            $i = 0;
            foreach ($queryProxy->getQuery()->iterate() as $pos => $object) {
                $entityManager->remove($object[0]);

                if ((++$i % 20) == 0) {
                    $entityManager->flush();
                    $entityManager->clear();
                }
            }

            $entityManager->flush();
            $entityManager->clear();
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        } catch (DBALException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }


    /**
     * @param Request|null $request
     * @return Response
     */
    public function listAction(Request $request = null)
    {
        if (false === $this->admin->checkAccess('list')) {
            throw new AccessDeniedException();
        }
        $galleryListMode = $request->get('pcode') ? true : false;

        $datagrid = $this->admin->getDatagrid();

        $persistentParameters = $this->admin->getPersistentParameters();

        $formView = $datagrid->getForm()->createView();


        $this->get('twig')->getRuntime(FormRenderer::class)->setTheme($formView, $this->admin->getFilterTheme());


        $tags = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();


        $tagAdmin = $this->get('networking_init_cms.admin.tag');

        return $this->render(
            $this->admin->getTemplate('list'),
            [
                'providers' => $this->get('sonata.media.pool')->getProvidersByContext(
                    $request->get('context', $persistentParameters['context'])
                ),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid,
                'galleryListMode' => $galleryListMode,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
                'show_actions' => true
            ]
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function refreshListAction(Request $request)
    {
        if (false === $this->admin->checkAccess('list')) {
            throw new AccessDeniedException();
        }
        $galleryListMode = $request->get('pcode') ? true : false;
        $datagrid = $this->admin->getDatagrid();
        $datagrid->getForm()->createView();
        $persistentParameters = $this->admin->getPersistentParameters();

        $tags = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->get('networking_init_cms.admin.tag');

        return $this->render(
            '@NetworkingInitCms/MediaAdmin/list_items.html.twig',
            [
                'providers' => $this->get('sonata.media.pool')->getProvidersByContext(
                    $request->get('context', $persistentParameters['context'])
                ),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'list',
                'datagrid' => $datagrid,
                'galleryListMode' => $galleryListMode,
                'show_actions' => true
            ]
        );
    }

}
