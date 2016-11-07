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

        if($this->isXmlHttpRequest()){
            $content = $response->getContent();
            $jsonArray = json_decode($content, true);

            if($jsonArray['result'] == 'ok'){
                $jsonArray['html'] = $this->getTagTree($jsonArray['objectId']);
                $response = $this->renderJson($jsonArray, 200);
            }
        }

        return $response;
    }

    /**
     * @param int|null|string $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteAction($id)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $returnToMedia = $request->get('returnToMedia');

        $id     = $this->get('request')->get($this->admin->getIdParameter());
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

            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'ok'));
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->admin->trans(
                        'flash_delete_success',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );

                if($returnToMedia){
                    $mediaAdmin = $this->get('sonata.media.admin.media');
                    $url = $mediaAdmin->generateUrl('list');
                    return new RedirectResponse($url);
                }
            } catch (ModelManagerException $e) {
                $this->logModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'error'));
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->admin->trans(
                        'flash_delete_error',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object);
        }


        return $this->render('NetworkingInitCmsBundle:TagAdmin:delete.html.twig', array(
            'object'     => $object,
            'action'     => 'delete',
            'returnToMedia' => $returnToMedia,
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ));
    }

    /**
     * @param ModelManagerException $e
     */
    private function logModelManagerException($e)
    {
        $context = array('exception' => $e);
        if ($e->getPrevious()) {
            $context['previous_exception_message'] = $e->getPrevious()->getMessage();
        }
        $this->getLogger()->error($e->getMessage(), $context);
    }


    /**
     * @param Request $request
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

            $messages = array();
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return new Response(join(', ', $messages), 400);
        }

        $this->admin->update($tag);

        return $this->renderJson(array(
            'result' => 'ok',
            'objectId' => $id,
        ));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function updateTreeAction(Request $request)
    {
        /** @var Request $request */
        $nodes = $request->get('nodes') ? $request->get('nodes') : array();

        $admin = $this->get('networking_init_cms.admin.tag');

        $validator = $this->get('validator');
        try {
            foreach ($nodes as $node) {
                if (!$node['item_id']) continue;
                /** @var $tag Tag */
                $tag = $admin->getObject($node['item_id']);
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

            $response = array('status' => 'success', 'message' => $this->admin->trans('info.tag_sorted'));
        } catch (\Exception $e) {
            $response = array('status' => 'error', 'message' => $this->admin->trans('info.tag_sorted_error'));
        }

        return $this->renderJson($response);
    }

    /**
     * @param $objectId
     * @return string
     */
    public function getTagTree($objectId)
    {
        $tagRepo = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Tag');
        $tags = $tagRepo->findBy(array('level' => 1), array('path' => 'ASC'));

        return $this->renderView('NetworkingInitCmsBundle:TagAdmin:tags.html.twig', array(
            'tags' => $tags,
            'tagAdmin' => $this->admin,
            'lastItem' => $objectId));
    }
}