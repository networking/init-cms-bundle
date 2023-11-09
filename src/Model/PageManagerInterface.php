<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Model;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ObjectRepository;
use Gedmo\Tree\RepositoryInterface;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class PageManagerInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageManagerInterface extends ContentRepositoryInterface, RepositoryInterface, ObjectRepository
{
    /**
     * @param null $id
     * @param bool $showHome
     * @param bool $showChildren
     */
    public function getParentPagesQuery($locale, $id = null, $showHome = false, $showChildren = false);

    /**
     * @param null $id
     */
    public function getParentPagesChoices($locale, $id = null);


    public function getAllSortBy($sort, $order = 'DESC', $hydrationMode = Query::HYDRATE_OBJECT);

    public function revertToPublished(PageInterface $draftPage, SerializerInterface $serializer);

    public function save(PageInterface $page);
}
