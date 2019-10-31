<?php

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
class AutoService implements AutoServiceInterface
{
    /**
     * @var string
     */
    public $admin;

    /**
     * @var string
     */
    public $controller;

    /**
     * @var string
     */
    public $group;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $labelTranslatorStrategy = 'sonata.admin.label.strategy.noop';

    /**
     * @var bool true
     */
    public $showInDashboard;

    /**
     * @var string SonataAdminBundle
     */
    public $labelCatalog;

    /**
     * @var string <i class="fa fa-folder"></i>
     */
    public $icon;
}