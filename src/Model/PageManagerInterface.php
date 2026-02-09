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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use Gedmo\Tree\RepositoryInterface;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class PageManagerInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 *
 * @method PageInterface|null find($id)
 * @method PageInterface|null findOneBy(array $criteria)
 * @method PageInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface PageManagerInterface extends ContentRepositoryInterface, RepositoryInterface, ObjectRepository
{
    public function getParentPagesQuery(string $locale, ?int $id = null, bool $showHome = false, bool $showChildren = false): QueryBuilder;

    public function getParentPagesChoices(string $locale, ?int $id = null): mixed;

    public function getAllSortBy(string $sort, string $order = 'DESC', string $hydrationMode = AbstractQuery::HYDRATE_OBJECT): mixed;

    public function revertToPublished(PageInterface $draftPage, SerializerInterface $serializer): mixed;

    public function save(PageInterface $page): mixed;
}
