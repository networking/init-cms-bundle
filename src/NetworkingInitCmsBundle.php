<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle;

use Networking\InitCmsBundle\DependencyInjection\Compiler\AddProviderCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Networking\InitCmsBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

/**
 * Class NetworkingInitCmsBundle
 * @package Networking\InitCmsBundle
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class NetworkingInitCmsBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new AddProviderCompilerPass());
    }

}
