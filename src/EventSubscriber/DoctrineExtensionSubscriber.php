<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\EventSubscriber;


use Gedmo\Blameable\BlameableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DoctrineExtensionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly BlameableListener $blameableListener, private readonly TokenStorageInterface $tokenStorage, private readonly TranslatableListener $translatableListener, private readonly LoggableListener $loggableListener)
    {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::FINISH_REQUEST => 'onLateKernelRequest'
        ];
    }
    public function onKernelRequest(): void
    {
        if ($this->tokenStorage !== null &&
            $this->tokenStorage->getToken() !== null &&
            $this->tokenStorage->getToken()->getUser() !== null
        ) {
            $this->blameableListener->setUserValue($this->tokenStorage->getToken()->getUser());
            $this->loggableListener->setUsername($this->tokenStorage->getToken());
        }
    }

    public function onLateKernelRequest(FinishRequestEvent $event): void
    {
        $this->translatableListener->setTranslatableLocale($event->getRequest()->getLocale());
    }

}
