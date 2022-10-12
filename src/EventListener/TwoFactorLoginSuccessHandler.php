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

namespace Networking\InitCmsBundle\EventListener;

use Networking\InitCmsBundle\GoogleAuthenticator\Helper;
use Networking\InitCmsBundle\GoogleAuthenticator\HelperInterface;
use Sonata\UserBundle\Model\User;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Twig\Environment;

/**
 * Class TwoFactorLoginSuccessHandler is used for handling 2FA authorization for enabled roles and ips.
 *
 * @author Aleksej Krichevsky <krich.al.vl@gmail.com>
 */
final class TwoFactorLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var Environment
     */
    private $engine;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Environment $engine,
        Helper $helper,
        UserManagerInterface $userManager,
        ?UrlGeneratorInterface $urlGenerator = null // NEXT_MAJOR: make it mandatory.
    ) {
        $this->engine = $engine;
        $this->helper = $helper;
        $this->userManager = $userManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return RedirectResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var $user User */
        $user = $token->getUser();
        $needToHave2FA = $this->helper->needToHaveGoogle2FACode($request);

        if ($needToHave2FA && !$user->getTwoStepVerificationCode()) {
            $url = $this->urlGenerator->generate('networking_init_cms_admin_two_factor_setup');
            return new RedirectResponse($url);
            
        } elseif ($needToHave2FA && $user->getTwoStepVerificationCode()) {
            $request->getSession()->set($this->helper->getSessionKey($token), null);
        }

        $url = $this->urlGenerator->generate('sonata_admin_dashboard');

        return new RedirectResponse($url);
    }
}
