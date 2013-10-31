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

use Gedmo\Tree\RepositoryInterface;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Class PageManagerInterface
 * @package Networking\InitCmsBundle\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageManagerInterface extends ContentRepositoryInterface, RepositoryInterface, ObjectRepository
{

    /**
     * @param $locale
     * @param  null $id
     * @return mixed
     */
    public function getParentPagesQuery($locale, $id = null);

    /**
     * @param $locale
     * @param null $id
     * @return mixed
     */
    public function getParentPagesChoices($locale, $id = null);

    /**
     * @param $sort
     * @param string $order
     * @return mixed
     */
    public function getAllSortBy($sort, $order = 'DESC');

    /**
     * @param $draftPage
     * @param $serializer
     * @return mixed
     */
    public function revertToPublished(PageInterface $draftPage, \JMS\Serializer\SerializerInterface $serializer);

    /**
     * @param PageInterface $page
     * @return mixed
     */
    public function save(PageInterface $page);

}
