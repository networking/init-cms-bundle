<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle;

use Networking\InitCmsBundle\DependencyInjection\Compiler\ClamAVCompilerPass;
use Networking\InitCmsBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Networking\InitCmsBundle\DependencyInjection\Compiler\MediaProviderCompilerPass;
use Networking\InitCmsBundle\DependencyInjection\Compiler\CheckSessionPass;
use Networking\InitCmsBundle\DependencyInjection\Compiler\SetAdminLanguagesPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Networking\InitCmsBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

/**
 * Class NetworkingInitCmsBundle.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class NetworkingInitCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideServiceCompilerPass());
        $container->addCompilerPass(new MediaProviderCompilerPass());
        $container->addCompilerPass(new CheckSessionPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new SetAdminLanguagesPass());
        $container->addCompilerPass(new ClamAVCompilerPass());
    }
}
