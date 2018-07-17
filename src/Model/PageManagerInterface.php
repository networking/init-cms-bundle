<?php
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
use Gedmo\Tree\RepositoryInterface;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Class PageManagerInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageManagerInterface extends ContentRepositoryInterface, RepositoryInterface, ObjectRepository
{
    /**
     * @param $locale
     * @param null $id
     * @param bool $showHome
     * @param bool $showChildren
     *
     * @return mixed
     */
    public function getParentPagesQuery($locale, $id = null, $showHome = false, $showChildren = false);

    /**
     * @param $locale
     * @param null $id
     *
     * @return mixed
     */
    public function getParentPagesChoices($locale, $id = null);

    /**
     * @param $sort
     * @param string $order
     * @param int    $hydrationMode
     *
     * @return mixed
     */
    public function getAllSortBy($sort, $order = 'DESC', $hydrationMode = Query::HYDRATE_OBJECT);

    /**
     * @param $draftPage
     * @param $serializer
     *
     * @return mixed
     */
    public function revertToPublished(PageInterface $draftPage, \JMS\Serializer\SerializerInterface $serializer);

    /**
     * @param PageInterface $page
     *
     * @return mixed
     */
    public function save(PageInterface $page);
}
