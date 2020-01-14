<?php
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
use Networking\InitCmsBundle\Model\Tag;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Exception\ValidatorException;

class TagAdminController extends CRUDController
{
    /**
     * @return Response
     */
    public function createAction()
    {
        /** @var Response $response */
        $response = parent::createAction();

        if ($this->isXmlHttpRequest()) {
            $content = $response->getContent();
            $jsonArray = json_decode($content, true);


            if ($jsonArray['result'] == 'ok') {
                $object = $this->admin->getObject($jsonArray['objectId']);
                $jsonArray['status'] =  'success';
                $jsonArray['message'] =  $this->translate('flash_create_success', ['%name%' =>  $this->escapeHtml($this->admin->toString($object))], 'SonataAdminBundle');
                $jsonArray['html'] = $this->getTagTree($jsonArray['objectId']);
                $response = $this->renderJson($jsonArray, 200);
            }
        }

        return $response;
    }

    /**
     * @param $objectId
     *
     * @return string
     */
    public function getTagTree($objectId)
    {
        $tagRepo = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Tag');
        $tags = $tagRepo->findBy(['level' => 1], ['path' => 'ASC']);

        return $this->renderView('@NetworkingInitCms/TagAdmin/tags.html.twig', [
            'noSort' => false,
            'tags' => $tags,
            'tagAdmin' => $this->admin,
            'lastItem' => $objectId, ]);
    }

    /**
     * @param int|null|string $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteAction($id)
    {
        $request = $this->getRequest();
        $returnToMedia = $request->get('returnToMedia');
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }

        if ($this->getRestMethod() == 'DELETE') {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');
            $translator = $this->get('translator');
            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'ok']);
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $translator->trans(
                        'flash_delete_success',
                        ['%name%' => $this->escapeHtml($this->admin->toString($object))],
                        'SonataAdminBundle'
                    )
                );

                if ($returnToMedia) {
                    $mediaAdmin = $this->get('sonata.media.admin.media');
                    $url = $mediaAdmin->generateUrl('list');

                    return new RedirectResponse($url);
                }
            } catch (ModelManagerException $e) {
                $this->logModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'error']);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $translator->trans(
                        'flash_delete_error',
                        ['%name%' => $this->escapeHtml($this->admin->toString($object))],
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object);
        }

        return $this->render('@NetworkingInitCms/TagAdmin/delete.html.twig', [
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
     * @param Request $request
     *
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

        $validator = $this->get('validator');
        $errors = $validator->validate($tag);

        if (count($errors) > 0) {
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
     * @param Request $request
     *
     * @return Response
     */
    public function updateTreeAction(Request $request)
    {
        /** @var Request $request */
        $nodes = $request->get('nodes') ? $request->get('nodes') : [];

        $admin = $this->get('networking_init_cms.admin.tag');

        $validator = $this->get('validator');
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
                if (count($errors) > 0) {
                    throw new ValidatorException();
                }

                $this->admin->update($tag);
            }

            $response = ['status' => 'success', 'message' => $this->admin->trans('info.tag_sorted')];
        } catch (\Exception $e) {
            $response = ['status' => 'error', 'message' => $this->admin->trans('info.tag_sorted_error')];
        }

        return $this->renderJson($response);
    }

    /**
     * @param Request $request
     *
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
}
