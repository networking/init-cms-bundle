<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Networking\InitCmsBundle\Action\MaintenanceAction;
use Networking\InitCmsBundle\Admin\LayoutBlockAdmin;
use Networking\InitCmsBundle\Admin\MediaAdmin;
use Networking\InitCmsBundle\Admin\PageAdmin;
use Networking\InitCmsBundle\Admin\TagAdmin;
use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReader;
use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReaderInterface;
use Networking\InitCmsBundle\Cache\PageCache;
use Networking\InitCmsBundle\Command\ClearCacheCommand;
use Networking\InitCmsBundle\Command\ClearLogCommand;
use Networking\InitCmsBundle\Command\MaintenanceDisableCommand;
use Networking\InitCmsBundle\Command\MaintenanceEnableCommand;
use Networking\InitCmsBundle\Command\SecurityCheckCommand;
use Networking\InitCmsBundle\Controller\AdminResettingController;
use Networking\InitCmsBundle\Controller\AdminSecurityController;
use Networking\InitCmsBundle\Controller\CkeditorAdminController;
use Networking\InitCmsBundle\Controller\HelpTextController;
use Networking\InitCmsBundle\Controller\InstallController;
use Networking\InitCmsBundle\Controller\MediaController;
use Networking\InitCmsBundle\Controller\TwoFactorController;
use Networking\InitCmsBundle\Controller\XmlController;
use Networking\InitCmsBundle\Entity\UserManager;
use Networking\InitCmsBundle\EventSubscriber\AdminToolbarSubscriber;
use Networking\InitCmsBundle\Filter\SimpleStringFilter;
use Networking\InitCmsBundle\Generator\MediaPathGenerator;
use Networking\InitCmsBundle\Helper\OneTimeCodeHelper;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Lib\ScannerFactory;
use Networking\InitCmsBundle\MessageHandler\UserMessageHandler;
use Networking\InitCmsBundle\Model\ContentRouteManagerInterface;
use Networking\InitCmsBundle\Model\HelpTextManagerInterface;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotManagerInterface;
use Networking\InitCmsBundle\Repository\OneTimeCodeRequestRepository;
use Networking\InitCmsBundle\Security\Voter\LayoutBlockVoter;
use Networking\InitCmsBundle\Serializer\LayoutBlockNormalizer;
use Networking\InitCmsBundle\Serializer\MediaNormalizer;
use Networking\InitCmsBundle\Serializer\PageNormalizer;
use Networking\InitCmsBundle\Thumbnail\FormatThumbnail;
use Networking\InitCmsBundle\Twig\Extension\ImageFilter;
use Networking\InitCmsBundle\Twig\Extension\NetworkingHelperExtension;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Cmf\Component\Routing\DynamicRouter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private();

    $services->load('Networking\\InitCmsBundle\\Controller\\', '../../Controller')
        ->exclude('../../Controller/{OneUploader,WebAuthn}')
        ->public()
        ->autowire()
        ->tag('controller.service_arguments');

    $services->load('Networking\\InitCmsBundle\\Twig\\Extension\\', '../../Twig/Extension')
        ->public()
        ->autowire()
        ->tag('twig.extension');

    $services->set(NetworkingHelperExtension::class)
        ->arg('$rolesBuilder', service('sonata.user.matrix_roles_builder'))
        ->arg('$templates', '%networking_init_cms.page.templates%')
        ->arg('$contentTypes', '%networking_init_cms.page.content_types%')
        ->tag('twig.extension');

    $services->alias('FOS\\CKEditorBundle\\Config\\CKEditorConfiguration', 'fos_ck_editor.configuration');

    $services->load('Networking\\InitCmsBundle\\Command\\', '../../Command/*');
    $services->load('Networking\\InitCmsBundle\\EventSubscriber\\', '../../EventSubscriber/*');

    $services->set(MediaController::class)
        ->arg('$mediaManager', service('sonata.media.manager.media'))
        ->arg('$pool', service('sonata.media.pool'));

    $services->set(MaintenanceAction::class)
        ->public()
        ->arg('$projectDir', '%kernel.project_dir%');

    $services->set(ClearLogCommand::class)
        ->public()
        ->arg('$pageClass', '%networking_init_cms.admin.page.class%');

    $services->set(MaintenanceEnableCommand::class)
        ->arg('$projectDir', '%kernel.project_dir%');

    $services->set(MaintenanceDisableCommand::class)
        ->arg('$projectDir', '%kernel.project_dir%');

    $services->set(ClearCacheCommand::class)
        ->autowire()
        ->public()
        ->arg('$managedLocales', '%lexik_translation.managed_locales%');

    $services->set(SecurityCheckCommand::class)
        ->autowire()
        ->public()
        ->arg('$projectDir', '%kernel.project_dir%');

    $services->set(TwoFactorController::class)
        ->autowire()
        ->public()
        ->arg('$userManager', service('networking_init_cms.user_manager'))
        ->tag('controller.service_arguments');

    $services->set(AdminSecurityController::class)
        ->autowire()
        ->public()
        ->arg('$templateRegistry', service('sonata.admin.global_template_registry'))
        ->tag('controller.service_arguments');

    $services->set(SimpleStringFilter::class)
        ->autowire()
        ->public()
        ->tag('sonata.admin.filter.type');

    $services->set(InstallController::class)
        ->autowire()
        ->public()
        ->arg('$userManager', service('networking_init_cms.user_manager'))
        ->tag('controller.service_arguments');

    $services->set(HelpTextController::class)
        ->autowire()
        ->public()
        ->arg('$templateRegistry', service('sonata.admin.global_template_registry'))
        ->tag('controller.service_arguments');

    $services->set(XmlController::class)
        ->autowire()
        ->public()
        ->arg('$pageClass', '%networking_init_cms.manager.page.class%')
        ->arg('$languages', '%networking_init_cms.page.languages%')
        ->arg('$additionalLinks', '%networking_init_cms.xml_sitemap.additional_links%')
        ->arg('$domainName', '%networking_init_cms.xml_sitemap.sitemap_url%')
        ->tag('controller.service_arguments');

    $services->set(PageHelper::class)
        ->autowire()
        ->private()
        ->arg('$allowLocaleCookie', '%networking_init_cms.allow_locale_cookie%')
        ->arg('$singleLanguage', '%networking_init_cms.single_language%');

    $services->alias('networking_init_cms.helper.page_helper', PageHelper::class)
        ->public();

    $services->set('networking_init_cms.logger', Logger::class)
        ->arg(0, 'newo')
        ->call('pushHandler', [service('networking_init_cms.handler')]);

    $services->set('networking_init_cms.handler', RotatingFileHandler::class)
        ->arg(0, '%kernel.logs_dir%/%kernel.environment%.changed_entities.log')
        ->arg(1, 7)
        ->arg(2, 200);

    $services->set(AdminToolbarSubscriber::class)
        ->arg('$mode', '%networking_init_cms.admin_toolbar.mode%');

    $services->set(PageCache::class)
        ->private()
        ->autowire()
        ->arg('$expiresAfter', '%networking_init_cms.cache.cache_time%')
        ->arg('$activated', '%networking_init_cms.cache.activate%');

    $services->set('networking_init_cms.cms_router', DynamicRouter::class)
        ->private()
        ->arg(0, service('router.request_context'))
        ->arg(1, service('cmf_routing.nested_matcher'))
        ->arg(2, service('cmf_routing.generator'))
        ->call('addRouteEnhancer', [service('cmf_routing.enhancer.route_content')])
        ->call('addRouteEnhancer', [service('cmf_routing.enhancer.controllers_by_class')]);

    $services->alias(DynamicRouter::class, 'networking_init_cms.cms_router')
        ->private();

    $services->set('networking_init_cms.media.generator.media_path', MediaPathGenerator::class);

    $services->set('networking_init_cms.media.thumbnail.format', FormatThumbnail::class)
        ->arg(0, 'jpg');

    $services->alias(UserManager::class, 'networking_init_cms.user_manager')
        ->private();

    $services->alias(PageManagerInterface::class, 'networking_init_cms.page_manager')
        ->private();

    $services->alias(PageSnapshotManagerInterface::class, 'networking_init_cms.page_snapshot_manager')
        ->private();

    $services->alias(MenuItemManagerInterface::class, 'networking_init_cms.menu_item_manager')
        ->private();

    $services->alias(HelpTextManagerInterface::class, 'networking_init_cms.help_text_manager')
        ->private();

    $services->alias(ContentRouteManagerInterface::class, 'networking_init_cms.content_route_manager')
        ->private();

    $services->alias(LayoutBlockAdmin::class, 'networking_init_cms.admin.layout_block')
        ->public();

    $services->alias(PageAdmin::class, 'networking_init_cms.admin.page')
        ->public();

    $services->alias(TagAdmin::class, 'networking_init_cms.admin.tag')
        ->public();

    $services->alias(MediaAdmin::class, 'sonata.media.admin.media')
        ->public();

    $services->alias(Pool::class, 'sonata.admin.pool')
        ->private();

    $services->alias(UserManagerInterface::class, 'networking_init_cms.user_manager');

    $services->set('networking_init_cms.attribute.reader', SonataAdminAttributeReader::class)
        ->autowire()
        ->public();

    $services->set('networking_init_cms.annotation.reader', SonataAdminAttributeReader::class)
        ->autowire()
        ->public();

    $services->alias(SonataAdminAttributeReaderInterface::class, 'networking_init_cms.attribute.reader')
        ->private();

    $services->set(LayoutBlockVoter::class)
        ->autowire()
        ->public()
        ->tag('security.voter', ['priority' => 300]);

    $services->set(ImageFilter::class)
        ->arg('$flysystem', service('oneup_flysystem.media_filesystem'))
        ->arg('$cacheFlysystem', service('oneup_flysystem.cache_filesystem'));

    $services->set(CkeditorAdminController::class)
        ->autowire()
        ->public()
        ->arg('$mediaAdmin', service('sonata.media.admin.media'))
        ->tag('controller.service_arguments');

    $services->set(AdminResettingController::class)
        ->autoconfigure()
        ->autowire()
        ->public()
        ->arg('$templateRegistry', service('sonata.admin.global_template_registry'))
        ->arg('$tokenGenerator', service('sonata.user.util.token_generator'))
        ->arg('$mailer', service('sonata.user.mailer.default'))
        ->tag('controller.service_arguments');

    $services->set(OneTimeCodeHelper::class)
        ->autoconfigure()
        ->autowire()
        ->public()
        ->arg('$signingKey', '%kernel.secret%');

    $services->set(OneTimeCodeRequestRepository::class)
        ->autowire()
        ->public();

    $services->set(MediaNormalizer::class)
        ->autowire()
        ->public()
        ->arg('$objectNormalizer', service('serializer.normalizer.object'))
        ->arg('$propertyNormalizer', service('serializer.normalizer.property'))
        ->arg('$mediaClass', '%sonata.media.media.class%')
        ->tag('serializer.normalizer', ['priority' => 1000]);

    $services->set(LayoutBlockNormalizer::class)
        ->autowire()
        ->public()
        ->arg('$objectNormalizer', service('serializer.normalizer.object'))
        ->arg('$propertyNormalizer', service('serializer.normalizer.property'))
        ->tag('serializer.normalizer', ['priority' => 1000]);

    $services->set(PageNormalizer::class)
        ->autowire()
        ->public()
        ->arg('$pageClass', '%networking_init_cms.admin.page.class%')
        ->arg('$objectNormalizer', service('serializer.normalizer.object'))
        ->tag('serializer.normalizer', ['priority' => 100]);

    $services->set(UserMessageHandler::class)
        ->autowire()
        ->public();

    $services->set('networking_init_cms.clamav_factory', ScannerFactory::class)
        ->autowire()
        ->public();
};
