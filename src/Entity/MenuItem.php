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

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Networking\InitCmsBundle\Model\MenuItemInterface;

/**
 * Class MenuItem.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[ORM\Entity]
#[ORM\Table(name: 'cms_menu')]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Tree(type: 'nested')]
class MenuItem extends BaseMenuItem
{
    #[ORM\ManyToOne(
        targetEntity: 'Networking\InitCmsBundle\Entity\MenuItem',
        inversedBy: 'children'
    )]
    #[ORM\JoinColumn(
        name: 'parent_id',
        referencedColumnName: 'id',
        onDelete: 'SET NULL'
    )]
    #[Gedmo\TreeParent]
    protected ?MenuItemInterface $parent = null;

    #[ORM\OneToMany(
        mappedBy: 'parent',
        targetEntity: 'Networking\InitCmsBundle\Entity\MenuItem',
    )]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected Collection $children;


}
