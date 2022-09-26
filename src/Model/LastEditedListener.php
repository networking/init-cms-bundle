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

use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Networking\InitCmsBundle\Helper\BundleGuesser;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class LastEditedListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var BundleGuesser
     */
    protected $bundleGuesser;

    /**
     * LastEditedListener constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->bundleGuesser = new BundleGuesser();
    }

    /**
     * @return array|void
     */
    public static function getSubscribedEvents()
    {
        return [
            'crud_controller.edit_entity' => 'registerEdited',
        ];
    }

    /**
     * @param CmsEvent $event
     * @return mixed
     */
    abstract public function registerEdited(CmsEvent $event);
}
