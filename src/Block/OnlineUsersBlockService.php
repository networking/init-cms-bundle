<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Block;

use Networking\InitCmsBundle\Model\UserManagerInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class OnlineUsersBlockService.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class OnlineUsersBlockService extends AbstractBlockService
{
    /**
     * @var
     */
    protected $userManager;

    /**
     * OnlineUsersBlockService constructor.
     * @param Environment $twig
     * @param UserManagerInterface $userManager
     */
    public function __construct(Environment $twig, UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
        parent::__construct($twig);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $users = $this->userManager->getLatestActivity();

        return $this->renderResponse(
            $blockContext->getTemplate(),
            [
                'block' => $blockContext->getBlock(),
                'online_users' => $users,
            ],
            $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Online Users Block';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'template' => '@NetworkingInitCms/Block/block_online_users.html.twig',
            ]
        );
    }
}
