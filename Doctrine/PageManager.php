<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Doctrine;

use Networking\InitCmsBundle\Model\PageInterface;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


