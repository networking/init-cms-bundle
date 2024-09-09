<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace Networking\InitCmsBundle\DependencyInjection\Compiler;

use Networking\InitCmsBundle\Provider\VimeoProvider;
use Networking\InitCmsBundle\Thumbnail\FormatThumbnail;
use Sonata\MediaBundle\Thumbnail\ResizableThumbnailInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Networking\InitCmsBundle\Provider\FileProvider;
use Networking\InitCmsBundle\Provider\ImageProvider;
use Networking\InitCmsBundle\Provider\YouTubeProvider;
use Symfony\Component\DependencyInjection\Reference;

class MediaProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {

        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            $definition = $container->getDefinition($id);

            if ('sonata.media.provider.image' == $id) {
                $definition->setClass(ImageProvider::class);

            }

            if ('sonata.media.provider.file' == $id) {
                $definition->setClass(FileProvider::class);
            }

            if ('sonata.media.provider.youtube' == $id) {
                $definition->setClass(YouTubeProvider::class);

            }

            if ('sonata.media.provider.vimeo' == $id) {
                $definition->setClass(VimeoProvider::class);
            }
        }

        if (!$container->hasDefinition('networking_init_cms.media.thumbnail.format')) {
            return;
        }

        $definition = $container->getDefinition(
            'networking_init_cms.media.thumbnail.format'
        );

        $resolvedClass = $container->getParameterBag()->resolveValue($definition->getClass());

        if (!is_a($resolvedClass, ResizableThumbnailInterface::class, true)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(
            'sonata.media.resizer'
        );

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addResizer',[$id, new Reference($id)]
            );
        }
    }

}
