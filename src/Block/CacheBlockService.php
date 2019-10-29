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
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class OnlineUsersBlockService.
 *
 * @author info@networking.ch
 */
class CacheBlockService extends AbstractBlockService
{
    /**
     * @var PhpCacheInterface
     */
    protected $cache;

    /**
     * CacheBlockService constructor.
     * @param Environment $twig
     * @param PhpCacheInterface $cache
     */
    public function __construct(Environment $twig, PhpCacheInterface $cache)
    {
        $this->cache = $cache;

        parent::__construct($twig);
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
                        'cache' => $this->cache,
                    ],
                    $response
                );
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
                'template' => '@NetworkingInitCms/Block/block_cache.html.twig',
            ]
        );
    }
}
