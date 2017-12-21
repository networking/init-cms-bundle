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
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OnlineUsersBlockService
 * @package Networking\InitCmsBundle\Block
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class OnlineUsersBlockService extends AbstractAdminBlockService
{

    /**
     * @var \Networking\InitCmsBundle\Model\UserManagerInterface
     */
    protected $um;

    /**
     * @param string $name
     * @param EngineInterface $templating
     */
    public function __construct($name, EngineInterface $templating)
    {
        $this->name = $name;
        $this->templating = $templating;
    }

    public function setUserManager(UserManagerInterface $um)
    {
        $this->um = $um;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $users = $this->um->getLatestActivity();

        return $this->renderResponse(
            $blockContext->getTemplate(),
            [
                'block' => $blockContext->getBlock(),
                'online_users' => $users
            ],
            $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        // TODO: Implement buildEditForm() method.
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
                'template' => 'NetworkingInitCmsBundle:Block:block_online_users.html.twig'
            ]
        );
    }
}