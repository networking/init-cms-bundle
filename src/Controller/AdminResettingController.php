<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Networking\InitCmsBundle\Entity\OneTimeCodeRequest;
use Networking\InitCmsBundle\Helper\OneTimeCodeHelper;
use Sonata\UserBundle\Form\Type\ResetPasswordRequestFormType;
use Sonata\UserBundle\Form\Type\ResettingFormType;
use Sonata\UserBundle\Mailer\MailerInterface;
use Sonata\UserBundle\Model\UserInterface;
use Sonata\UserBundle\Model\UserManagerInterface;
use Sonata\UserBundle\Util\TokenGeneratorInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

class AdminResettingController extends AbstractController
{

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private RouterInterface $router,
        private Pool $pool,
        private UserManagerInterface $userManager,
        private TemplateRegistryInterface $templateRegistry,
        private TokenGeneratorInterface $tokenGenerator,
        private FormFactoryInterface $formFactory,
        private TranslatorInterface $translator,
        private MailerInterface $mailer,
        private OneTimeCodeHelper $oneTimeCodeHelper,
        private ?int $resettingTokenTTL = 86400,
        private ?int $retryTTL = 7200,
    ) {
    }

    /**
     * @return Response
     */
    public function requestAction(Request $request)
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse(
                $this->router->generate('sonata_admin_dashboard')
            );
        }
        $form = $this->formFactory->create(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $user = $this->userManager->findUserByUsernameOrEmail($username);

            if (null !== $user && $user->isEnabled()
                && !$user->isPasswordRequestNonExpired($this->retryTTL)
                && $user->isAccountNonLocked()
            ) {
                if (null === $user->getConfirmationToken()) {
                    $user->setConfirmationToken(
                        $this->tokenGenerator->generateToken()
                    );
                }

                $this->mailer->sendResettingEmailMessage($user);
                $user->setPasswordRequestedAt(new \DateTime());
                $this->userManager->save($user);
            }

            return new RedirectResponse(
                $this->router->generate(
                    'networking_init_cms_admin_resetting_check_email',
                    [
                        'username' => $username,
                    ]
                )
            );
        }

        return $this->render(
            '@NetworkingInitCms/Admin/Security/Resetting/request.html.twig',
            [
                'base_template' => $this->templateRegistry->getTemplate(
                    'layout'
                ),
                'admin_pool' => $this->pool,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function sendEmailAction(Request $request)
    {
        throw new \Exception('This method is not implemented');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function checkEmailAction(Request $request)
    {
        $username = $request->query->get('username');

        if (empty($username)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse(
                $this->generateUrl(
                    'networking_init_cms_admin_resetting_request'
                )
            );
        }

        return $this->render(
            '@NetworkingInitCms/Admin/Security/Resetting/checkEmail.html.twig',
            [
                'base_template' => $this->templateRegistry->getTemplate(
                    'layout'
                ),
                'admin_pool' => $this->pool,
                'tokenLifetime' => ceil($this->retryTTL / 3600),
            ]
        );
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function resetAction(Request $request, $token)
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        if (!$user->isPasswordRequestNonExpired($this->retryTTL)) {
            return new RedirectResponse($this->router->generate('sonata_user_admin_resetting_request'));
        }

        $form = $this->formFactory->create(ResettingFormType::class);
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $user->setEnabled(true);

            /**
             * TODO: Use instanceof FlashBagAwareSessionInterface when dropping Symfony 5 support.
             *
             * @phpstan-ignore-next-line
             * @psalm-suppress UndefinedInterfaceMethod
             */
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->translator->trans('resetting.flash.success', [], 'SonataUserBundle')
            );

            $response = new RedirectResponse($this->router->generate('sonata_admin_dashboard'));

            $this->userManager->save($user);

            return $response;
        }

        return $this->render(
            '@NetworkingInitCms/Admin/Security/Resetting/reset.html.twig',
            [
                'token' => $token,
                'form' => $form->createView(),
                'base_template' => $this->templateRegistry->getTemplate('layout'),
                'admin_pool' => $this->pool,
            ]
        );
    }

    public function sendEmailCodeAction(Request $request, #[CurrentUser] UserInterface $user): Response
    {
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "username" does not exist for value "%s"', $user->getUsername()));
        }

        return $this->processSendingOneTimeCodeRequestEmail($request, $user);
    }

    protected function processSendingOneTimeCodeRequestEmail(Request $request, UserInterface $user)
    {
        try {
            $oneTimeCodeRequest = $this->oneTimeCodeHelper->generateOneTimeCodeRequest($user);
        } catch (ResetPasswordExceptionInterface $e) {

            return $this->redirectToRoute('app_check_email');
        }
        
        try{
            $this->oneTimeCodeHelper->sendOneTimeCodeRequestEmail($user, $oneTimeCodeRequest);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }


        $request->getSession()->set('networing_init_cms.one_time_code', [
            'user_id' => $user->getId(),
            'requested_at' => $oneTimeCodeRequest->getRequestedAt()->getTimestamp(),
        ]);

        return new JsonResponse(['success' => true]);
    }
}
