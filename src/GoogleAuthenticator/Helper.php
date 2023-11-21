<?php

declare(strict_types=1);


namespace Networking\InitCmsBundle\GoogleAuthenticator;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Helper implements HelperInterface
{
    /**
     * @var Google2FA
     */
    protected $authenticator;

    /**
     * @param string[] $trustedIpList IPs that will bypass 2FA authorization
     * @param string $server
     * @param string[] $forcedForRoles
     */
    public function __construct(
        protected $server,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly array $forcedForRoles = [],
        private readonly array $trustedIpList = []
    ) {
        $this->authenticator = new Google2FA();
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
        return $this->authenticator->verifyKey($secret, $code);
    }

    /**
     * @return string
     */
    public function getUrl(UserInterface $user)
    {

        return $this->getUrlFromSecret($user, $user->getTwoStepVerificationCode());
    }

    /**
     * @return string
     */
    public function getUrlFromSecret(UserInterface $user, $secret)
    {

        $qrCodeUrl = $this->authenticator->getQRCodeUrl(
            $this->server,
            $user->getUsername(),
            $secret
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(400),
                new SvgImageBackEnd()
            )
        );

        return 'data:image/svg+xml;base64,'. base64_encode($writer->writeString($qrCodeUrl));

    }

    /**
     * @return string
     */
    public function generateSecret()
    {
        return $this->authenticator->generateSecretKey();
    }

    public function getSessionKey(AbstractToken $token): string
    {
        return sprintf('networking_init_cms_google_authenticator_%s_%s', $token->getFirewallName(), $token->getUserIdentifier());
    }

    public function getVerifySessionKey(AbstractToken $token): string
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
