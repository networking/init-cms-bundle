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

use Sonata\UserBundle\Form\Factory\FactoryInterface;
use Sonata\UserBundle\Mailer\MailerInterface;
use Sonata\UserBundle\Model\UserInterface;
use Sonata\UserBundle\Model\UserManagerInterface;
use Sonata\UserBundle\Util\TokenGeneratorInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminResettingController extends AbstractController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MailerInterface
     */
    private $mailer;

    private $loginManager;
    private $resettingFormFactory;
    private $retryTTL;
    private $firewallName;
    private $resettingTokenTTL;
    private $fromEmail;
    private $emailTemplate;

//    /**
//     * AdminResettingController constructor.
//     * @param AuthorizationCheckerInterface $authorizationChecker
//     * @param RouterInterface $router
//     * @param Pool $pool
//     * @param UserManagerInterface $userManager
//     * @param TemplateRegistryInterface $templateRegistry
//     * @param TokenGeneratorInterface $tokenGenerator
//     * @param FactoryInterface $resettingFormFactory
//     * @param TranslatorInterface $translator
//     * @param MailerInterface $mailer
//     * @param $retryTTL
//     * @param $firewallName
//     * @param $resettingTokenTTL
//     * @param $fromEmail
//     * @param $emailTemplate
//     */
//    public function __construct(
//        AuthorizationCheckerInterface $authorizationChecker,
//        RouterInterface $router,
//        Pool $pool,
//        UserManagerInterface $userManager,
//        TemplateRegistryInterface $templateRegistry,
//        TokenGeneratorInterface $tokenGenerator,
//        FactoryInterface $resettingFormFactory,
//        TranslatorInterface $translator,
//        MailerInterface $mailer,
//        $retryTTL,
//        $firewallName,
//        $resettingTokenTTL,
//        $fromEmail,
//        $emailTemplate
//
//    )
//    {
//
//        $this->authorizationChecker = $authorizationChecker;
//        $this->router = $router;
//        $this->pool = $pool;
//        $this->userManager = $userManager;
//        $this->templateRegistry = $templateRegistry;
//        $this->tokenGenerator = $tokenGenerator;
//        $this->loginManager = $loginManager;
//        $this->resettingFormFactory = $resettingFormFactory;
//        $this->translator = $translator;
//        $this->mailer = $mailer;
//        $this->retryTTL = $retryTTL;
//        $this->firewallName = $firewallName;
//        $this->resettingTokenTTL = $resettingTokenTTL;
//        $this->fromEmail = $fromEmail;
//        $this->emailTemplate = $emailTemplate;
//    }

    /**
     * @return Response
     */
    public function requestAction()
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse($this->router->generate('sonata_admin_dashboard'));
        }

        return $this->render('NetworkingInitCmsBundle:Admin:Security/Resetting/request.html.twig', [
            'base_template' => $this->templateRegistry->getTemplate('layout'),
            'admin_pool' => $this->pool,
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Exception
     */
    public function sendEmailAction(Request $request)
    {
        $username = $request->request->get('username');

        $userManager = $this->userManager;

        $user = $userManager->findUserByUsernameOrEmail($username);

        if (null !== $user && !$user->isPasswordRequestNonExpired($this->retryTTL)) {
            if (!$user->isAccountNonLocked()) {
                return new RedirectResponse($this->get('router')->generate('networking_init_cms_admin_resetting_request'));
            }

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

//            $this->sendResettingEmailMessage($user);
            $this->mailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $userManager->updateUser($user);
        }

        if($request->isXmlHttpRequest()){

//            if($user){
                $data = [
                    'message' => nl2br($this->translator->trans('resetting.check_email', ['%tokenLifetime%' => $this->retryTTL / 3600], 'FOSUserBundle')),
                    'link' => $this->generateUrl('networking_init_cms_admin_security_login')
                ];
                $status = 200;
//            }else{
//                $data = [
//                    'message' => nl2br($this->translator->trans('resetting.check_email', ['%tokenLifetime%' => $this->retryTTL / 3600], 'FOSUserBundle')),
//                    'link' => $this->generateUrl('networking_init_cms_admin_security_login')
//                ];
//                $status = 422;
//            }
            return new JsonResponse($data, $status);
        }



        return new RedirectResponse($this->generateUrl('networking_init_cms_admin_resetting_check_email', [
            'username' => $username,
        ]));
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
            return new RedirectResponse($this->generateUrl('networking_init_cms_admin_resetting_request'));
        }

        return $this->render('NetworkingInitCmsBundle:Admin:Security/Resetting/checkEmail.html.twig', [
            'base_template' => $this->templateRegistry->getTemplate('layout'),
            'admin_pool' => $this->pool,
            'tokenLifetime' => ceil($this->retryTTL / 3600),
        ]);
    }

    /**
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function resetAction(Request $request, $token)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new RedirectResponse($this->get('router')->generate('sonata_admin_dashboard'));
        }



        $user = $this->userManager->findUserByConfirmationToken($token);

        $firewallName = $this->container->getParameter('fos_user.firewall_name');

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        if (!$user->isPasswordRequestNonExpired($this->resettingTokenTTL)) {
            return new RedirectResponse($this->generateUrl('sonata_user_admin_resetting_request'));
        }

        $form = $this->resettingFormFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setConfirmationToken(null);
            $user->setPasswordRequestedAt(null);
            $user->setEnabled(true);

            $message = $this->get('translator')->trans('resetting.flash.success', [], 'FOSUserBundle');
            $this->addFlash('success', $message);
            $response = new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));

            try {
//                $this->loginManager->logInUser($firewallName, $user, $response);
                $user->setLastLogin(new \DateTime());
            } catch (AccountStatusException $ex) {
                // We simply do not authenticate users which do not pass the user
                // checker (not enabled, expired, etc.).
                if ($this->has('logger')) {
                    $this->get('logger')->warning(sprintf(
                        'Unable to login user %d after password reset',
                        $user->getId())
                    );
                }
            }

            $this->userManager->updateUser($user);

            return $response;
        }

        return $this->render('NetworkingInitCmsBundle:Admin:Security/Resetting/reset.html.twig', [
            'token' => $token,
            'form' => $form->createView(),
            'base_template' => $this->templateRegistry->getTemplate('layout'),
            'admin_pool' => $this->pool,
        ]);
    }

    /**
     * Send an email to a user to confirm the password reset.
     *
     * @param UserInterface $user
     */
    private function sendResettingEmailMessage(UserInterface $user): void
    {
        $url = $this->generateUrl('networking_init_cms_admin_resetting_reset', [
            'token' => $user->getConfirmationToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $rendered = $this->renderView($this->emailTemplate, [
            'user' => $user,
            'confirmationUrl' => $url,
        ]);

        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode(PHP_EOL, trim($rendered));
        $subject = array_shift($renderedLines);
        $body = implode(PHP_EOL, $renderedLines);
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom($this->fromEmail)
            ->setTo((string) $user->getEmail())
            ->setBody($body);
        $this->get('mailer')->send($message);
    }
}
