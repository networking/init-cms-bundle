<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('networking_init_cms_default', '/')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::homeAction');

    $routes->add('networking_init_cms_home', '/')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::homeAction');

    $routes->add('networking_init_cms_admin_no_slash', '/admin')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::adminAction')
        ->options(['expose' => true]);

    $routes->add('networking_init_cms_admin', '/admin/')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::adminAction');

    $routes->add('networking_init_cms_admin_security_login', '/admin/login')
        ->controller('Networking\InitCmsBundle\Controller\AdminSecurityController::loginAction');

    $routes->add('networking_init_cms_admin_security_check', '/admin/login_check')
        ->controller('Networking\InitCmsBundle\Controller\AdminSecurityController::checkAction')
        ->methods(['POST']);

    $routes->add('networking_init_cms_admin_two_factor_setup', '/admin/two_factor_setup')
        ->controller('Networking\InitCmsBundle\Controller\TwoFactorController::setTwoFactorAuthentication')
        ->methods(['GET', 'POST']);

    $routes->add('networking_init_cms_admin_security_logout', '/admin/logout')
        ->controller('Networking\InitCmsBundle\Controller\AdminSecurityController::logoutAction');

    $routes->add('networking_init_change_admin_language', '/change_admin_language/{locale}')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::changeAdminLanguageAction')
        ->methods(['GET', 'POST'])
        ->requirements(['locale' => '.+']);

    $routes->add('networking_init_change_language', '/change_language/{oldLocale}/{locale}')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::changeLanguageAction')
        ->methods(['GET', 'POST'])
        ->requirements(['locale' => '.+']);

    $routes->add('networking_init_view_draft_no_param', '/view_draft')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::viewDraftAction');

    $routes->add('networking_init_view_draft', '/view_draft/{locale}/{path}')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::viewDraftAction')
        ->requirements(['locale' => '(.*)?', 'path' => '(.*)?']);

    $routes->add('networking_init_view_live', '/view_live/{locale}/{path}')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::viewLiveAction')
        ->requirements(['locale' => '(.*)?', 'path' => '(.*)?']);

    $routes->add('networking_init_view_live_no_param', '/view_live')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::viewLiveAction');

    $routes->add('networking_init_cms_help', '/admin/help/{adminCode}/{action}')
        ->controller('Networking\InitCmsBundle\Controller\HelpTextController::adminHelpAction')
        ->requirements(['adminCode' => '.+', 'action' => '(.*)?']);

    $routes->add('networking_init_cms_help_dashboard', '/admin/help/{adminCode}')
        ->controller('Networking\InitCmsBundle\Controller\HelpTextController::adminHelpAction')
        ->requirements(['adminCode' => '.+']);

    $routes->add('networking_init_cms_file_download', '/file/download/{id}/{name}')
        ->controller('Networking\InitCmsBundle\Controller\MediaController::downloadAction')
        ->defaults(['format' => 'reference'])
        ->requirements(['name' => '(.*)?', 'format' => '(.*)?']);

    $routes->add('networking_init_cms_file_view', '/file/view/{id}/{name}')
        ->controller('Networking\InitCmsBundle\Controller\MediaController::viewAction')
        ->defaults(['format' => 'reference'])
        ->requirements(['name' => '(.*)?', 'format' => '(.*)?']);

    $routes->add('networking_init_cms_image_view', '/image/view/{id}/{format}/{name}')
        ->controller('Networking\InitCmsBundle\Controller\MediaController::viewAction')
        ->defaults(['format' => 'reference'])
        ->requirements(['name' => '(.*)?', 'format' => '(.*)?']);

    $routes->add('networking_init_cms_image_view_no_format', '/image/view/{id}/{name}')
        ->controller('Networking\InitCmsBundle\Controller\MediaController::viewAction')
        ->defaults(['format' => 'reference'])
        ->requirements(['name' => '(.*)?']);

    $routes->add('networking_init_cms_clear_cache', '/cache/clear')
        ->controller('Networking\InitCmsBundle\Controller\CacheController::clearAction');

    $routes->add('initcms_no_translation', '/no_translation')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::translationNotFoundAction');

    $routes->add('initcms_404', '/404')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::pageNotFoundAction');

    $routes->add('xml_sitemap', '/xml_sitemap/{locale}')
        ->controller('Networking\InitCmsBundle\Controller\XmlController::siteMapAction')
        ->defaults(['locale' => ''])
        ->methods(['GET']);

    $routes->add('_initcms_admin_navbar', '/admin/_initcms_admin_navbar/{page_id}')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::adminNavbarAction');

    $routes->add('networking_init_cms_admin_login', '/admin/login{trailingSlash}')
        ->controller('Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction')
        ->defaults(['path' => '/admin/', 'permanent' => true])
        ->requirements(['trailingSlash' => '[/]{1}']);

    $routes->add('networking_init_cms_page_url_json', '/cms/pages/internal-url.json')
        ->controller('Networking\InitCmsBundle\Controller\FrontendPageController::getJsonUrlsAction');

    $routes->add('networking_init_cms_admin_resetting_request', '/admin/request')
        ->controller('Networking\InitCmsBundle\Controller\AdminResettingController::requestAction')
        ->methods(['GET', 'POST']);

    $routes->add('networking_init_cms_admin_resetting_check_email', '/admin/check-email')
        ->controller('Networking\InitCmsBundle\Controller\AdminResettingController::checkEmailAction')
        ->methods(['GET']);

    $routes->add('networking_init_cms_admin_resetting_reset', '/admin/reset/{token}')
        ->controller('Networking\InitCmsBundle\Controller\AdminResettingController::resetAction')
        ->methods(['GET', 'POST']);

    $routes->add('cms_api_login', '/admin/api/login')
        ->controller('Networking\InitCmsBundle\Controller\AdminSecurityController::apiLogin')
        ->options(['expose' => true]);

    $routes->add('networking_init_cms_admin_send_one_time_code', '/admin/one_time_code')
        ->controller('Networking\InitCmsBundle\Controller\AdminResettingController::sendEmailCodeAction')
        ->methods(['GET']);
};
