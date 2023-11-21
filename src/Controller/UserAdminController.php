<?php

namespace Networking\InitCmsBundle\Controller;

use App\Entity\WebauthnCredential;
use Doctrine\Persistence\ManagerRegistry;
use Jenssegers\Agent\Agent;
use Networking\InitCmsBundle\Admin\Extension\UserProfileExtension;
use Networking\InitCmsBundle\Entity\BaseUser as User;
use Networking\InitCmsBundle\GoogleAuthenticator\HelperInterface;
use Networking\InitCmsBundle\Model\UserInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\Bundle\Security\Authentication\Token\WebauthnToken;

class UserAdminController extends SonataCRUDController
{

    public function __construct(
        private readonly HelperInterface $helper,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly PublicKeyCredentialSourceRepositoryInterface $publicKeyCredentialSourceRepository,
        private readonly PublicKeyCredentialUserEntityRepositoryInterface $publicKeyCredentialUserEntityRepository,
    )
    {
    }

    public function profileSecurityAction(Request $request, #[CurrentUser] UserInterface $user): Response {

        if(!$this->canEditUser($user)){
            return $this->render('@NetworkingInitCms/Admin/Security/security_settings_impersonator.html.twig');
        }

        $userEntity = $this->publicKeyCredentialUserEntityRepository->findOneByUsername($user->getUserIdentifier());
        $passkeys = $this->publicKeyCredentialSourceRepository->findAllForUserEntity($userEntity);


        $this->admin->setSubject($user);
        $this->admin->addExtension(new UserProfileExtension());

        $form = $this->admin->getForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $submittedObject = $form->getData();
                $existingObject = $this->admin->update($submittedObject);

                if ($this->isXmlHttpRequest($request)) {
                    return $this->handleXmlHttpRequestSuccessResponse($request, $existingObject);
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_edit_success',
                        ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                        'SonataAdminBundle'
                    )
                );

                // redirect to edit mode
                return new RedirectResponse($this->admin->generateUrl('profile_security'));
            } catch (ModelManagerException $e) {
                // NEXT_MAJOR: Remove this catch.
                $this->handleModelManagerException($e);

                $isFormValid = false;
            } catch (ModelManagerThrowable $e) {
                $errorMessage = $this->handleModelManagerThrowable($e);

                $isFormValid = false;
            } catch (LockException) {
                $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', [
                    '%name%' => $this->escapeHtml($this->admin->toString($existingObject)),
                    '%link_start%' => sprintf('<a href="%s">', $this->admin->generateObjectUrl('edit', $existingObject)),
                    '%link_end%' => '</a>',
                ], 'SonataAdminBundle'));
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        return $this->render(
            '@NetworkingInitCms/Admin/Security/security_settings.html.twig',
            [
                'hasStepVerificationCode' => $user->hasStepVerificationCode(),
                'form' => $formView,
                'qrCodeUrl' => $this->helper->getUrl($user),
                'qrSecret' => $user->getTwoStepVerificationCode(),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'passkeys' => $passkeys,
            ]
        );
    }

    public function profileSecurityGetAuthenticatorAction(Request $request, #[CurrentUser] UserInterface $user): JsonResponse
    {

        if(!$this->canEditUser($user)){
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid user'], 400);
        }

        $authenticator = ['hasStepVerificationCode'=> false];
        if($user->hasStepVerificationCode()){
            $authenticator = [
                'secret' => $user->getTwoStepVerificationCode(),
                'qrCodeUrl' => $this->helper->getUrl($user),
                'hasStepVerificationCode' => true
            ];
        }

        return new JsonResponse($authenticator);
    }



    public function profileSecurityCreateAuthenticatorAction(Request $request, #[CurrentUser] UserInterface $user): JsonResponse
    {

        if(!$this->canEditUser($user)){
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid user'], 400);
        }

        $secret = $this->helper->generateSecret();

        $sessionKey = $this->helper->getSessionKey($this->tokenStorage->getToken());

        $request->getSession()->set($sessionKey, $secret);

        return new JsonResponse(['secret' => $secret, 'qrCodeUrl' => $this->helper->getUrlFromSecret($user, $secret)]);

    }

    public function profileSecurityVerifyAuthenticatorAction(Request $request, #[CurrentUser] UserInterface $user): JsonResponse
    {
        if(!$this->canEditUser($user)){
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid user'], 400);
        }

        $secret = $request->getSession()->get($this->helper->getSessionKey($this->tokenStorage->getToken()), null);
        $state = 'success';
        if ($secret && 'POST' === $request->getMethod()) {
            if (true === $this->helper->checkSecret($secret, $request->get('_code'))) {
                $user->setTwoStepVerificationCode($secret);
                $this->admin->update($user);

                return new JsonResponse(['status' => 'ok']);
            }

        }

        return new JsonResponse(['status' => 'error', 'message' => 'Invalid code'], 400);
    }



    public function getWebauthnKeysAction(Request $request, #[CurrentUser] User $user)
    {
        if (!$user) {
            return new JsonResponse([]);
        }

        if(!$this->canEditUser($user)){
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid user'], 400);
        }

        $userEntity
            = $this->publicKeyCredentialUserEntityRepository->findOneByUsername(
            $user->getUserIdentifier()
        );

        /** @var WebauthnCredential[] $results */
        $results
            = $this->publicKeyCredentialSourceRepository->findAllForUserEntity(
            $userEntity
        );
        $tokens = [];

        $defaultIcon = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="19" height="35" viewBox="0 0 19 35" fill="none">
<path d="M18.2499 9.09761C18.2556 7.47387 17.8282 5.87841 17.0125 4.47793C16.1967 3.07745 15.0225 1.92337 13.6126 1.13629C12.2027 0.349206 10.6088 -0.0419933 8.99745 0.00357083C7.38613 0.0491349 5.81653 0.529789 4.45266 1.39531C3.0888 2.26083 1.98074 3.47944 1.24426 4.92381C0.507784 6.36818 0.16993 7.98529 0.266001 9.60615C0.362072 11.227 0.888542 12.7921 1.7904 14.138C2.69225 15.4838 3.93639 16.561 5.3928 17.2569V31.1146L9.24995 35L15.6785 28.5244L11.8214 24.639L15.6785 20.7537L12.4899 17.5418C14.1834 16.8836 15.6397 15.7249 16.6677 14.2179C17.6956 12.7109 18.2472 10.9261 18.2499 9.09761ZM9.24995 9.09761C8.74137 9.09761 8.24421 8.9457 7.82134 8.66108C7.39847 8.37646 7.06888 7.97192 6.87426 7.49862C6.67963 7.02531 6.62871 6.5045 6.72793 6.00204C6.82715 5.49959 7.07205 5.03805 7.43167 4.6758C7.79129 4.31355 8.24948 4.06685 8.74828 3.9669C9.24709 3.86696 9.76412 3.91826 10.234 4.1143C10.7039 4.31035 11.1055 4.64235 11.388 5.06831C11.6706 5.49428 11.8214 5.99507 11.8214 6.50737C11.8214 7.19435 11.5505 7.85318 11.0682 8.33895C10.586 8.82471 9.93193 9.09761 9.24995 9.09761Z" fill="#363A3D"/>
</svg>');
        foreach ($results as $key => $value) {
            $data = $value->otherUI;
            $tokens[] = [
                'id' => $value->getId(),
                'name' => !empty($data) && array_key_exists('name', $data)?$data['name']:'Unnamed',
                'createdAt' => $data['createdAt']->format('D, M Y H:i:s'),
                'icon' => array_key_exists('icon_light', $data)?$data['icon_light']:$defaultIcon,
            ];
        }

        return new JsonResponse($tokens);
    }

    public function renameWebauthnKeyAction(
        Request $request,
        #[CurrentUser] ?User $user
    ) {
        if (!$user) {
            return new JsonResponse([]);
        }

        if(!$this->canEditUser($user)){
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid user'], 400);
        }

        $credentialId = $request->get('credId');
        $name = $request->get('name');

        $webAuthToken = $this->publicKeyCredentialSourceRepository->findOneBy(
            ['userHandle' => $user->getUserIdentifier(), 'id' => $credentialId]
        );

        $data = $webAuthToken->otherUI;

        $data['name'] = $name;

        $webAuthToken->otherUI = $data;

        if ($webAuthToken) {
            $this->entityManager->persist($webAuthToken);
            $this->entityManager->flush();
        }


        return new JsonResponse([]);
    }

    public function removeWebauthnKeyAction(
        Request $request,
        #[CurrentUser] User $user
    ) {
        if (!$user) {
            return new JsonResponse([]);
        }

        if(!$this->canEditUser($user)){
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid user'], 400);
        }

        $credentialId = $request->get('credId');

        $webAuthToken = $this->publicKeyCredentialSourceRepository->findOneBy(
            ['userHandle' => $user->getUserIdentifier(), 'id' => $credentialId]
        );

        if ($webAuthToken) {
            $this->publicKeyCredentialSourceRepository->removeCredentialSource($webAuthToken);
        }
        return new JsonResponse([]);
    }

    private function canEditUser(UserInterface $user): bool
    {
        if (!$this->isGranted('IS_IMPERSONATOR')) {
            return $this->getUser()->getUserIdentifier() === $user->getUserIdentifier();
        }

        $isSuperAdmin = false;

        $impersonator = $this->tokenStorage->getToken()->getOriginalToken();

        $isSuperAdmin = array_reduce($impersonator->getRoleNames(), function ($hasRole, $role) {
            if( $role === 'ROLE_SUPEdR_ADMIN'){
                $hasRole = true;
            }

            return $hasRole;
        }, false);


        return $isSuperAdmin;

    }
}
