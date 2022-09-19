<?php
declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Block;

use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
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
     * @var PageCacheInterface
     */
    protected $cache;

    /**
     * CacheBlockService constructor.
     * @param Environment $twig
     * @param PageCacheInterface $cache
     */
    public function __construct(Environment $twig, PageCacheInterface $cache)
    {
        $this->cache = $cache;

        parent::__construct($twig);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
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
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'template' => '@NetworkingInitCms/Block/block_cache.html.twig',
            ]
        );
    }
}
