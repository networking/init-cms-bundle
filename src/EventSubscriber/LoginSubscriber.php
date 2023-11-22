<?php
declare(strict_types=1);

namespace Networking\InitCmsBundle\EventSubscriber;

use Networking\InitCmsBundle\Message\UserMessage;
use Networking\InitCmsBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly ?PublicKeyCredentialUserEntityRepositoryInterface $userEntityRepository,
        private readonly ?PublicKeyCredentialSourceRepositoryInterface $credentialSourceRepository,
    ){

    }

    public function onSuccessfulLogin(LoginSuccessEvent $event): void
    {
        $token = $event->getAuthenticatedToken();
        if (!$token) {
            return;
        }

        $userEntity = $this->userEntityRepository->findOneByUsername($token->getUserIdentifier());

        $credentials = $this->credentialSourceRepository->findAllForUserEntity($userEntity);

        if (!count($credentials)) {
            /** @var UserInterface $user */
            $user = $token->getUser();
            $this->messageBus->dispatch(new UserMessage($token->getUserIdentifier(), 'No webauthn token found for this user.'));
            return;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onSuccessfulLogin',
        ];
    }
}