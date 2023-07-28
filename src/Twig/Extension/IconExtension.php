<?php

declare(strict_types=1);

/*
 * This file is part of the MopaBootstrapBundle.
 *
 * (c) Philipp A. Mohrenweiser <phiamo@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Twig\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TemplateWrapper;
use Twig\TwigFunction;

/**
 */
class IconExtension extends AbstractExtension
{
    /**
     * @var TemplateWrapper|null
     */
    protected $iconTemplate;



    public function getFunctions(): array
    {
        $options = [
            'is_safe' => ['html'],
            'needs_environment' => true,
        ];

        $functions = [
            new TwigFunction('networking_bootstrap_icon', $this->renderIcon(...), $options),
        ];

        return $functions;
    }

    /**
     * Renders the icon.
     *
     * @param string $icon
     * @param bool   $inverted
     */
    public function renderIcon(Environment $env, $icon, $inverted = false, $iconset = 'fontawesome4'): string
    {
        $template = $this->getIconTemplate($env);
        $context = [
            'icon' => $icon,
            'inverted' => $inverted,
        ];

        return $template->renderBlock($iconset, $context);
    }

    protected function getIconTemplate(Environment $env): TemplateWrapper
    {
        if ($this->iconTemplate === null) {
            $this->iconTemplate = $env->load('@NetworkingInitCms/icons.html.twig');
        }

        return $this->iconTemplate;
    }
}
