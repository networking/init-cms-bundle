<?php


namespace Networking\InitCmsBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Flex\Recipe;

class CheckSessionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('session')) {
            $message = 'InitCmsBundle requires the "session" service to be available.';

            if (class_exists(Recipe::class)) {
                $message .= ' Uncomment the "session" section in "config/packages/framework.yaml" to activate it.';
            }

            throw new \LogicException($message);
        }
    }
}