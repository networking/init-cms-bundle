<?php
declare(strict_types=1);

namespace Networking\InitCmsBundle\MessageHandler;

use Networking\InitCmsBundle\Message\UserMessage;
use Networking\InitCmsBundle\Model\UserInterface;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UserMessageHandler
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function __invoke(UserMessage $userMessage): void
    {
        /** @var UserInterface */
        $user = $this->userManager->findUserByUsername($userMessage->getUsername());

        if (!$user) {
            return;
        }

        $user->setAdminSetting('notification', $userMessage->getMessage());

        $this->userManager->updateUser($user);
    }
}