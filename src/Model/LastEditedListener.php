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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Networking\InitCmsBundle\Helper\BundleGuesser;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class LastEditedListener implements EventSubscriberInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var BundleGuesser
     */
    protected $bundleGuesser;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
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
}
