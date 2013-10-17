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

use  Symfony\Cmf\Component\Routing\RouteReferrersReadInterface,
     Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageSnapshotInterface extends RouteReferrersReadInterface, ResourceVersionInterface{

    /**
     * @param $resourceId
     *
     * @return PageSnapshot
     */
    public function setResourceId($resourceId);


    /**
     * @return int
     */
    public function getResourceId();

    /**
     * @param $resourceName
     *
     * @return PageSnapshot
     */
    public function setResourceName($resourceName);


    /**
     * @return string
     */
    public function getResourceName();


    /**
     * @param $snapshotDate
     *
     * @return PageSnapshot
     */
    public function setSnapshotDate($snapshotDate);

    /**
     * @return \DateTime
     */
    public function getSnapshotDate();


    /**
     * @param $version
     *
     * @return PageSnapshot
     */
    public function setVersion($version);

    /**
     * @return int
     */
    public function getVersion();

    /**
     * @param $versionedData
     *
     * @return PageSnapshot
     */
    public function setVersionedData($versionedData);

    /**
     * @return mixed
     */
    public function getVersionedData();

    /**
     * @param int $id
     *
     * @return PageSnapshot
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();


    /**
     * Set page
     *
     * @param  BasePage $page
     *
     * @return PageSnapshot
     */
    public function setPage(BasePage $page);

    /**
     * Get conversation
     *
     * @return Page
     */
    public function getPage();


    /**
     * @param  ContentRoute $contentRoute
     * @return PageSnapshot
     */
    public function setContentRoute(ContentRoute $contentRoute);


    /**
     * @return ContentRoute
     */
    public function getContentRoute();

    /**
     * @return Page
     */
    public function getOriginal();



    /**
     * @param $path
     */
    public function setPath($path);


    /**
     * @return mixed
     */
    public function getPath();


}