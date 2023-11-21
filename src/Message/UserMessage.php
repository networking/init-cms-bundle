<?php

namespace Networking\InitCmsBundle\Message;

class UserMessage
{
    public function __construct(
        private string $username,
        private string $message
    ) {

    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}