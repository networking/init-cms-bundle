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

use Networking\InitCmsBundle\Lib\PhpCacheInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class OnlineUsersBlockService
 * @package Networking\InitCmsBundle\Block
 * @author info@networking.ch
 */
class CacheBlockService extends AbstractAdminBlockService
{
    /**
     * @var PhpCacheInterface
     */
    protected $cache;

    /**
     * CacheBlockService constructor.
     * @param string $name
     * @param EngineInterface $templating
     * @param PhpCacheInterface $cache
     */
    public function __construct(string $name, EngineInterface $templating, PhpCacheInterface $cache)
    {
        $this->cache = $cache;

        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse(
                    $blockContext->getTemplate(),
                    [
                        'block' => $blockContext->getBlock(),
                        'cache' => $this->cache
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
        return 'Cache Block';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'template' => 'NetworkingInitCmsBundle:Block:block_cache.html.twig'
            ]
        );

    }
}