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

namespace Networking\InitCmsBundle\GoogleAuthenticator;

use Sonata\GoogleAuthenticator\GoogleAuthenticator as BaseGoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Helper implements HelperInterface
{
    /**
     * @var BaseGoogleAuthenticator
     */
    protected $authenticator;

    /**
     * @param string[] $trustedIpList IPs that will bypass 2FA authorization
     * @param string $server
     * @param string[] $forcedForRoles
     */
    public function __construct(
        protected $server,
        BaseGoogleAuthenticator $authenticator,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly array $forcedForRoles = [],
        private readonly array $trustedIpList = []
    ) {
        $this->authenticator = $authenticator;
    }

    /**
     * @param $code
     *
     * @return bool
     */
    public function checkCode(UserInterface $user, $code)
    {
        return $this->checkSecret($user->getTwoStepVerificationCode(), $code);
    }

    /**
     * @param $code
     *
     * @return bool
     */
    public function checkSecret($secret, $code)
    {
        return $this->authenticator->checkCode($secret, $code);
    }

    /**
     * @return string
     */
    public function getUrl(UserInterface $user)
    {
       return GoogleQrUrl::generate($user->getUsername(),  $user->getTwoStepVerificationCode(), $this->server);
    }

    /**
     * @return string
     */
    public function getUrlFromSecret(UserInterface $user, $secret)
    {
        return GoogleQrUrl::generate($user->getUsername(),  $secret, $this->server);
    }

    /**
     * @return string
     */
    public function generateSecret()
    {
        return $this->authenticator->generateSecret();
    }

    public function getSessionKey(UsernamePasswordToken $token): string
    {
        return sprintf('networking_init_cms_google_authenticator_%s_%s', $token->getFirewallName(), $token->getUserIdentifier());
    }

    public function getVerifySessionKey(UsernamePasswordToken $token): string
    {
        return sprintf('networking_init_cms_google_authenticator_verify__%s_%s', $token->getFirewallName(), $token->getUserIdentifier());
    }

    public function needToHaveGoogle2FACode(Request $request): bool
    {
        if (\in_array($request->getClientIp(), $this->trustedIpList, true)) {
            return false;
        }

        if($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')){
            return false;
        }

        foreach ($this->forcedForRoles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }

        return false;
    }
}
