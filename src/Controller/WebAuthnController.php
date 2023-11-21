<?php

namespace Networking\InitCmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Jenssegers\Agent\Agent;
use Networking\InitCmsBundle\Entity\BaseUser as User;
use Networking\InitCmsBundle\Entity\UserManager;
use Networking\InitCmsBundle\GoogleAuthenticator\Helper;
use Networking\InitCmsBundle\GoogleAuthenticator\HelperInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Uid\Uuid;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\Bundle\Controller\AssertionRequestController;
use Webauthn\Bundle\Controller\AssertionResponseController;
use Webauthn\Bundle\CredentialOptionsBuilder\ProfileBasedRequestOptionsBuilder;
use Webauthn\Bundle\Exception\HttpNotImplementedException;
use Webauthn\Bundle\Exception\MissingFeatureException;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\Bundle\Security\Authorization\Voter\IsUserPresentVoter;
use Webauthn\Bundle\Security\Authorization\Voter\IsUserVerifiedVoter;
use Webauthn\Bundle\Security\Storage\OptionsStorage;
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

class WebAuthnController extends AbstractController
{

    public function __construct(
        private readonly PublicKeyCredentialLoader $publicKeyCredentialLoader,
        private readonly AuthenticatorAttestationResponseValidator $attestationResponseValidator,
        private readonly PublicKeyCredentialUserEntityRepositoryInterface $publicKeyCredentialUserEntityRepository,
        private readonly PublicKeyCredentialSourceRepositoryInterface $publicKeyCredentialSourceRepository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly OptionsStorage $optionsStorage,
        private readonly EntityManagerInterface $entityManager,
        private readonly Pool $pool,
        private readonly TemplateRegistryInterface $templateRegistry,

    ) {
    }


    #[Route('/admin/register', name: 'initcms_webauthn_register_response', methods: ['POST'], options: ['expose' => true])]
    public function registerResponse(
        Request $request,
        #[CurrentUser] ?User $user
    ) {

        try {
            $content = $request->getContent();
            $publicKeyCredential = $this->publicKeyCredentialLoader->load(
                $content
            );

            $response = $publicKeyCredential->response;
            $response instanceof AuthenticatorAttestationResponse
            || throw InvalidDataException::create(
                $response,
                'Invalid response'
            );

            $aaguid = $response->attestationObject->authData->attestedCredentialData->aaguid;

            $storedData = $this->optionsStorage->get(
                $response->clientDataJSON->challenge
            );
            $publicKeyCredentialCreationOptions = $storedData->getPublicKeyCredentialOptions();
            $publicKeyCredentialCreationOptions instanceof PublicKeyCredentialCreationOptions || throw InvalidDataException::create(
                $publicKeyCredentialCreationOptions,
                'Unable to find the public key credential creation options'
            );
            $userEntity = $storedData->getPublicKeyCredentialUserEntity();
            $userEntity !== null || throw InvalidDataException::create(
                $userEntity,
                'Unable to find the public key credential user entity'
            );


            $credentialSource = $this->attestationResponseValidator->check(
                $response,
                $publicKeyCredentialCreationOptions,
                $request->getHost(),
                []
            );




            if ($this->publicKeyCredentialSourceRepository->findOneByCredentialId(
                $credentialSource->publicKeyCredentialId
            ) !== null
            ) {
                throw InvalidDataException::create(
                    $credentialSource,
                    'The credentials already exists'
                );
            }
            $content = file_get_contents(__DIR__.'/../Resources/config/aaguid.json');
            $loadedJson = json_decode($content, true);
            $aaguidStr = $aaguid->toRfc4122();
            if('00000000-0000-0000-0000-000000000000' === $aaguidStr){

                $agent = new Agent(
                    $request->headers->all(),
                    $request->headers->get('User-Agent')
                );

                if($agent->is('OS X')){
                    $aaguidStr = '00000000-0000-0000-0000-000000000001';
                }

                if($agent->is('iOS')){
                    $aaguidStr = '00000000-0000-0000-0000-000000000002';
                }

                $aaguid = new Uuid($aaguidStr);
            }


            if(array_key_exists($aaguidStr, $loadedJson)){
                $credentialSource->otherUI = $loadedJson[$aaguidStr];
                $credentialSource->aaguid = $aaguid;
            }

            $credentials = $this->publicKeyCredentialSourceRepository->findAllForUserEntityAndAaguid(
                $userEntity,
                $aaguid->toRfc4122()
            );

            if(count($credentials) > 0){
                $credentialSource->otherUI['name'] = $credentialSource->otherUI['name'].' '.(count($credentials) );
            }

            $credentialSource->otherUI['createdAt'] = new \DateTime();


            $this->publicKeyCredentialSourceRepository->saveCredentialSource(
                $credentialSource,
            );

            return new JsonResponse(['status' => 'ok']);
        } catch (\Throwable $e) {
            if ($e instanceof MissingFeatureException) {
                throw new HttpNotImplementedException($e->getMessage(), $e);
            }

            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
