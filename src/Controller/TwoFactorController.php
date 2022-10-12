<?php

namespace Networking\InitCmsBundle\Controller;

use Networking\InitCmsBundle\GoogleAuthenticator\Helper;
use Networking\InitCmsBundle\GoogleAuthenticator\HelperInterface;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TwoFactorController extends AbstractController
{

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }


    public function setTwoFactorAuthentication(Request $request, TokenStorageInterface $tokenStorage)
    {
        try{
            $helper= $this->get('networking_init_cms.google.authenticator.helper');
        }catch (ServiceNotFoundException $e){
            return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
        }

        $token = $tokenStorage->getToken();
        $user = $token->getUser();
        $secret = $request->getSession()->get($helper->getVerifySessionKey($token), null);
        $state = 'success';
        if($secret && 'POST' === $request->getMethod()){
            if (true === $helper->checkSecret($secret, $request->get('_code'))) {
                $user->setTwoStepVerificationCode($secret);
                $this->userManager->updateUser($user);

                return new RedirectResponse($this->generateUrl('sonata_admin_dashboard'));
            }

            $state = 'error';
        }

        if(!$secret){
            $secret = $helper->generateSecret();
        }

        $request->getSession()->set($helper->getVerifySessionKey($token), $secret);
        $qrCodeUrl = $helper->getUrlFromSecret($user, $secret);

        return $this->render(
            '@NetworkingInitCms/Admin/Security/two_step_form.html.twig',
            [
                'hasStepVerificationCode' => $user->hasStepVerificationCode(),
                'qrCodeUrl' => $qrCodeUrl,
                'qrSecret' => $secret,
                'state' => $state,
                'base_template' => '@NetworkingInitCms/admin_layout.html.twig',
                'error' => [],
            ]
        );
    }

    public static function getSubscribedServices()
    {
        return [
                'networking_init_cms.google.authenticator.helper' => '?'.HelperInterface::class,
            ] + parent::getSubscribedServices(
        );
    }
}