<?php

namespace Networking\InitCmsBundle\Repository;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Networking\InitCmsBundle\Entity\OneTimeCodeRequest;
use Networking\InitCmsBundle\Model\UserInterface;

/**
 * @extends ServiceEntityRepository<OneTimeCodeRequest>
 *
 * @method OneTimeCodeRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method OneTimeCodeRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method OneTimeCodeRequest[]    findAll()
 * @method OneTimeCodeRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OneTimeCodeRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OneTimeCodeRequest::class);
    }

    public function save(OneTimeCodeRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OneTimeCodeRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function createOneTimeCodeRequest(object $user, \DateTimeInterface $expiresAt, string $code, string $hashedToken): OneTimeCodeRequest
    {
        return new OneTimeCodeRequest($user, $expiresAt, $code, $hashedToken);
    }


    public function persistOneTimeCodeRequest(OneTimeCodeRequest $oneTimeCodeRequest): void
    {
        $this->getEntityManager()->persist($oneTimeCodeRequest);
        $this->getEntityManager()->flush();
    }

    public function findOneTimeCodeRequest(string $code, UserInterface $user): ?OneTimeCodeRequest
    {
        return $this->findOneBy(['code' => $code, 'userHandle' => $user->getUserIdentifier()]);
    }

    public function getMostRecentNonExpiredRequestDate(UserInterface $user): ?\DateTimeInterface
    {
        /** @var OneTimeCodeRequest $oneTimeCodeRequest */
        $oneTimeCodeRequest = $this->createQueryBuilder('t')
            ->where('t.userHandle = :user_handle')
            ->setParameter('user_handle', $user->getUserIdentifier())
            ->orderBy('t.requestedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneorNullResult()
        ;

        if (null !== $oneTimeCodeRequest && !$oneTimeCodeRequest->isExpired()) {
            return $oneTimeCodeRequest->getRequestedAt();
        }

        return null;
    }

    public function removeOneTimeCodeRequest(OneTimeCodeRequest $oneTimeCodeRequest): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.userHandle = :user_handle')
            ->setParameter('user_handle', $oneTimeCodeRequest->getUserHandle())
            ->getQuery()
            ->execute()
        ;
    }

    public function removeExpiredOneTimeCodeRequests(): int
    {
        $time = new \DateTimeImmutable('-1 week');
        $query = $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt <= :time')
            ->setParameter('time', $time)
            ->getQuery()
        ;

        return $query->execute();
    }
}
