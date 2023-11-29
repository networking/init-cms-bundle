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

use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @var MenuItemInterface
     */
    #[ORM\ManyToOne(
        targetEntity: 'Networking\InitCmsBundle\Entity\MenuItem',
        inversedBy: 'children'
    )]
    #[ORM\JoinColumn(
        name: 'parent_id',
        referencedColumnName: 'id',
        onDelete: 'SET NULL'
    )]
    #[Gedmo\TreeParent()]
    protected $parent;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(
        targetEntity: 'Networking\InitCmsBundle\Entity\MenuItem',
        mappedBy: 'parent',
        fetch: 'LAZY',
    )]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected $children;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'lft')]
    #[Gedmo\TreeLeft()]
    protected $lft;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'lvl')]
    #[Gedmo\TreeLevel()]
    protected $lvl;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'rgt')]
    #[Gedmo\TreeRight()]
    protected $rgt;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', name: 'root')]
    #[Gedmo\TreeRoot()]
    protected $root;
}
