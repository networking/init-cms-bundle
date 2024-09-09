<?php

declare(strict_types=1);

/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 *
 * Networking\InitCmsBundle\Entity\Group
 *
 */
#[ORM\Entity]
#[ORM\Table(name: 'user_group')]
class Group implements \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;
    
    #[ORM\Column(type: 'simple_array')]
    #[Assert\Count(min: 1, minMessage: 'user_group_empty_roles')]
    protected array $roles = [];

    #[ORM\Column(type: 'string', length: 255)]
    protected string $name;
    

    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = strtoupper((string) $role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper((string) $role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }


    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = User::ROLE_DEFAULT;

        return array_values(array_unique($roles));
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role): bool
    {
        return \in_array(strtoupper((string) $role), $this->getRoles(), true);
    }


    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }
}
