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

use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface ContentRouteInterface extends  RouteReferrersReadInterface
{
    /**
     * Set the content of a route
     * @param RouteReferrersReadInterface $content
     * @return ContentRoute
     */
    public function setContent(RouteReferrersReadInterface $content);

    /**
     * @param  string $controller
     * @return $this
     */
    public function setController($controller);

    /**
     * @return string
     */
    public function getController();

    /**
     * @param  int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param  string $locale
     * @return $this
     */
    public function setLocale($locale);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @param  string $classType
     * @return $this
     */
    public function setClassType($classType);

    /**
     * @return string
     */
    public function getClassType();

    /**
     * @param  int $objectId
     * @return $this
     */
    public function setObjectId($objectId);

    /**
     * @return int
     */
    public function getObjectId();

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param  string $template
     * @return $this
     */
    public function setTemplate($template);

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @param string $templateName
     */
    public function setTemplateName($templateName);

    /**
     * @return string
     */
    public function getTemplateName();

    /**
     * @return array
     */
    public function getDefaults();

}