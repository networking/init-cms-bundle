<?php

declare(strict_types=1);

/**
 * This file is part of the demo_cms  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Doctrine\ORM\Query;
use Networking\InitCmsBundle\Admin\MediaAdmin;
use Networking\InitCmsBundle\Entity\Tag;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagAdminController extends CRUDController
{
    public function createAction(Request $request): Response
    {
        /** @var Response $response */
        $response = parent::createAction($request);

        if ($this->isXmlHttpRequest($request) && 200 === $response->getStatusCode()) {
            $content = $response->getContent();
            try {
                $jsonArray = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                if ($jsonArray && 'ok' == $jsonArray['result']) {
                    $object = $this->admin->getObject($jsonArray['objectId']);
                    $jsonArray['status'] = 'success';
                    $jsonArray['message'] = $this->translate('flash_create_success', ['%name%' => $this->escapeHtml($this->admin->toString($object))], 'SonataAdminBundle');
                    $jsonArray['json'] = $this->admin->getTagTree($object->getId());
                    $response = $this->renderJson($jsonArray, 200);
                }
            } catch (\Exception) {
                return $response;
            }
        }

        return $response;
    }

    protected function handleXmlHttpRequestErrorResponse(Request $request, FormInterface $form): ?JsonResponse
    {
        return null;
    }

    /**
     * @return string
     */
    public function getTagTree($objectId)
    {
        $tags = $this->admin->getModelManager()->findBy(Tag::class, ['level' => 1], ['path' => 'ASC']);

        return $this->renderView('@NetworkingInitCms/TagAdmin/tags.html.twig', [
            'noSort' => false,
            'tags' => $tags,
            'tagAdmin' => $this->admin,
            'lastItem' => $objectId, ]);
    }

    /**
     * @return RedirectResponse|Response
     */
    public function deleteAction(Request $request): Response
    {
        $returnToMedia = $request->get('returnToMedia');
        $object = $this->assertObjectExists($request, true);
        \assert(null !== $object);

        $this->checkParentChildAssociation($request, $object);

        $this->admin->checkAccess('delete', $object);

        $preResponse = $this->preDelete($request, $object);
        if (null !== $preResponse) {
            return $preResponse;
        }

        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_DELETE], true)) {
            // check the csrf token
            $this->validateCsrfToken($request, 'sonata.delete');
            $translator = $this->container->get('translator');
            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest($request)) {
                    return $this->renderJson(['result' => 'ok']);
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_delete_success',
                        ['%name%' => $this->escapeHtml($this->admin->toString($object))],
                        'SonataAdminBundle'
                    )
                );

                if ($returnToMedia) {
                    $mediaAdmin = $this->container->get('sonata.media.admin.media');
                    $url = $mediaAdmin->generateUrl('list');

                    return new RedirectResponse($url);
                }
            } catch (ModelManagerException $e) {
                $this->logModelManagerException($e);

                if ($this->isXmlHttpRequest($request)) {
                    return $this->renderJson(['result' => 'error']);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        ['%name%' => $this->escapeHtml($this->admin->toString($object))],
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object);
        }

        return $this->renderWithExtraParams('@NetworkingInitCms/TagAdmin/delete.html.twig', [
            'object' => $object,
            'action' => 'delete',
            'returnToMedia' => $returnToMedia,
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ]);
    }

    /**
     * @param ModelManagerException $e
     */
    private function logModelManagerException($e)
    {
        $context = ['exception' => $e];
        if ($e->getPrevious()) {
            $context['previous_exception_message'] = $e->getPrevious()->getMessage();
        }
        $this->getLogger()->error($e->getMessage(), $context);
    }

    /**
     * @return Response
     */
    public function inlineEditAction(Request $request)
    {
        $id = $request->get('pk');

        $name = $request->get('value');

        /** @var $tag Tag */
        if (!$tag = $this->admin->getObject($id)) {
            throw new NotFoundHttpException('unable to find the tag with the id');
        }

        $tag->setName($name);

        $validator = $this->container->get('validator');
        $errors = $validator->validate($tag);

        if ((is_countable($errors) ? count($errors) : 0) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return new Response(implode(', ', $messages), 400);
        }

        $this->admin->update($tag);

        return $this->renderJson([
            'result' => 'ok',
            'objectId' => $id,
        ]);
    }

    /**
     * @return Response
     */
    public function updateTreeAction(Request $request)
    {
        /** @var Request $request */
        $nodes = $request->get('nodes') ?: [];

        $admin = $this->admin;

        $validator = $this->container->get('validator');
        try {
            foreach ($nodes as $node) {
                if (!$node['id']) {
                    continue;
                }
                /** @var $tag Tag */
                $tag = $admin->getObject($node['id']);
                if ($node['parent_id']) {
                    $parent = $admin->getObject($node['parent_id']);
                    $tag->setParent($parent);
                } else {
                    $tag->setParent(null);
                }

                $tag->setLevel($node['depth'] + 1);

                $errors = $validator->validate($tag);
                if ((is_countable($errors) ? count($errors) : 0) > 0) {
                    throw new ValidatorException();
                }

                $this->admin->update($tag);
            }

            $response = ['status' => 'success', 'message' => $this->trans('info.tag_sorted')];
        } catch (\Exception) {
            $response = ['status' => 'error', 'message' => $this->trans('info.tag_sorted_error')];
        }

        return $this->renderJson($response);
    }

    /**
     * @return Response
     */
    public function searchTagsAction(Request $request)
    {
        $q = $request->get('q');
        $response = [];
        $query = $this->admin->getModelManager()->createQuery($this->admin->getClass(), 't');
        $query->select('t.id, t.path AS text');
        $query->andWhere('t.path LIKE :q');
        $query->orderBy('text', 'ASC');
        $query->setParameter(':q', '%'.$q.'%');
        $response = $query->execute([], Query::HYDRATE_ARRAY);

        return $this->renderJson($response);
    }

    public static function getSubscribedServices(): array
    {
        return [
            'sonata.media.admin.media' => MediaAdmin::class,
            'validator' => ValidatorInterface::class,
        ] + parent::getSubscribedServices();
    }
}
