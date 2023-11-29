<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Networking\InitCmsBundle\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserActivityListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserActivityListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @throws \Exception
     */
    public function onCoreController(ControllerEvent $event)
    {
        // do not capture admin cms urls
        if (!preg_match('/.*\/admin\/.*/', $event->getRequest()->getRequestUri())) {
            return;
        }

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof UserInterface) {

            //here we can update the user as necessary
            if (method_exists($user, 'setLastActivity')) {
                try {
                    $classMetaData = $this->em->getClassMetadata($user::class);
                    $table = $classMetaData->getTableName();
                    $identifier = $classMetaData->getSingleIdentifierColumnName();
                    $updatedAtCol = $classMetaData->getColumnName('updatedAt');

                    $conn = $this->em->getConnection();
                    $sql = "UPDATE {$table} SET {$updatedAtCol} = :date WHERE {$identifier} = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue('date', (new \DateTime('now'))->format( 'Y-m-d H:i:s'));
                    $stmt->bindValue('id', $user->getId());
                    $stmt->executeStatement();
                } catch (ORMException) {
                    //do nothing, entity manager is closed
                }
            }
        }
    }
}
