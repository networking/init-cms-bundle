<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\GoogleAuthenticator;

use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

interface HelperInterface
{

    public function checkCode(UserInterface $user, $code);

    /**
     * @param $code
     *
     * @return bool
     */
    public function checkSecret($secret, $code);

    /**
     * @return string
     */
    public function getUrl(UserInterface $user);

    /**
     * @return string
     */
    public function getUrlFromSecret(UserInterface $user, $secret);

    /**
     * @return string
     */
    public function generateSecret();
    /**
     * @return string
     */
    public function getSessionKey(AbstractToken $token);

    /**
     * @return string
     */
    public function getVerifySessionKey(AbstractToken $token);

    public function needToHaveGoogle2FACode(Request $request): bool;
}
