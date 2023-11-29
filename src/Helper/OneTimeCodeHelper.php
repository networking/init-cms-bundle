<?php

namespace Networking\InitCmsBundle\Helper;

use Networking\InitCmsBundle\Entity\OneTimeCodeRequest;
use Networking\InitCmsBundle\Model\UserInterface;
use Networking\InitCmsBundle\Repository\OneTimeCodeRequestRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class OneTimeCodeHelper
{
    public function __construct(
        private OneTimeCodeRequestRepository $oneTimeCodeRequestRepository,
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private readonly string $signingKey,
        private ?string $fromEmailAddress = null,
        private ?string $fromName = null,
        private int $oneTimeCodeTTL = 8600,
    ) {
    }

    public function generateOneTimeCodeRequest(UserInterface $user): OneTimeCodeRequest
    {
        $code = $this->generateOneTimeCode();

        $expiresAt = new \DateTimeImmutable(sprintf('+%d seconds', $this->oneTimeCodeTTL));

        $encodedData = json_encode([$user->getUserIdentifier(), $expiresAt->getTimestamp()]);

        $hashedCode = $this->getHashedToken($encodedData);
        $this->oneTimeCodeRequestRepository->removeExpiredOneTimeCodeRequests();
        $oneTimeCodeRequest = $this->oneTimeCodeRequestRepository->createOneTimeCodeRequest($user, $expiresAt, $code, $hashedCode);


        $this->oneTimeCodeRequestRepository->save($oneTimeCodeRequest, true);

        return $oneTimeCodeRequest;
    }

    public function sendOneTimeCodeRequestEmail(UserInterface $user, OneTimeCodeRequest $oneTimeCodeRequest): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmailAddress, $this->fromName))
            ->to($user->getEmail())
            ->subject($this->translator->trans('one_time_code.email.subject', [], 'security'))
            ->htmlTemplate('@NetworkingInitCms/Admin/Security/OneTimeCode/email.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'code' => str_split($oneTimeCodeRequest->getCode())
            ]);

        $this->mailer->send($email);
    }

    public function removeOneTimeCodeRequest(string $code, UserInterface $user): void
    {
        $oneTimeCodeRequest = $this->oneTimeCodeRequestRepository->findOneTimeCodeRequest($code, $user);
        $this->oneTimeCodeRequestRepository->removeOneTimeCodeRequest($oneTimeCodeRequest);
    }

    public function checkCode(string $code, UserInterface $user): bool
    {
        $oneTimeCodeRequest = $this->oneTimeCodeRequestRepository->findOneTimeCodeRequest($code, $user);
        if (!$oneTimeCodeRequest) {
            return false;
        }


        if ($oneTimeCodeRequest->getExpiresAt() < new \DateTimeImmutable('now')) {

            $this->oneTimeCodeRequestRepository->remove($oneTimeCodeRequest, true);
            return false;
        }

        $encodedData = json_encode([$user->getUserIdentifier(), $oneTimeCodeRequest->getExpiresAt()->getTimestamp()]);
        $hashedCode = $this->getHashedToken($encodedData);

        if ($hashedCode !== $oneTimeCodeRequest->getHashedToken()) {
            return false;
        }

        if ($oneTimeCodeRequest->getCode() !== $code) {
            return false;
        }

        return true;
    }

    private function getHashedToken(string $data): string
    {
        return base64_encode(hash_hmac('sha256', $data, $this->signingKey, true));
    }

    private function generateOneTimeCode(): string
    {
        return substr(str_shuffle('0123456789'), 1, 6);
    }
}
