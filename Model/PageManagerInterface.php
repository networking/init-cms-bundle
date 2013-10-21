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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageManagerInterface extends ContentRepositoryInterface, ContainerAwareInterface, RepositoryInterface
{

    /**
     * @param $locale
     * @param  null                       $id
     * @return \Doctrine\ORM\QueryBuilder
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

}
