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


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Flex\Recipe;

class CheckSessionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('session.handler')) {
            $message = 'InitCmsBundle requires the "session" service to be available.';

            if (class_exists(Recipe::class)) {
                $message .= ' Uncomment the "session" section in "config/packages/framework.yaml" to activate it.';
            }

            throw new \LogicException($message);
        }
    }
}