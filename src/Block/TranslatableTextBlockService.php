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

use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class TranslatableTextBlockService.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslatableTextBlockService extends AbstractBlockService
{
    /**
     * SitemapBlockService constructor.
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        parent::__construct($twig);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        return $this->renderResponse(
            '@NetworkingInitCms/Block/block_translatable_text.html.twig',
            [
                'block' => $blockContext->getBlock(),
                'settings' => $blockContext->getSettings(),
            ],
            $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Translatable Text Block';
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'translation_key' => 'Insert your translation key',
                'translation_domain' => 'Insert the name of the translation domain',
            ]
        );
    }
}
