<?php
/**
 * This file is part of the demo_cms package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Controller;

use Ibrows\SonataTranslationBundle\Controller\TranslationCRUDController as IbrowsTranslationCRUDController;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationCRUDController extends IbrowsTranslationCRUDController
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function clearCacheAction()
    {
        $languages = $this->container->getParameter('networking_init_cms.page.languages');
        $localeChoices = array($this->getUser()->getLocale());
        foreach ($languages as $language) {
            $localeChoices[] = $language['locale'];
        }

        $this->get('translator')->removeLocalesCacheFiles($localeChoices);

        /** @var \Networking\InitCmsBundle\Lib\PhpCacheInterface $phpCache */
        $phpCache = $this->get('networking_init_cms.lib.php_cache');
        if ($phpCache->isActive()) {
            $phpCache->clean();
        }

        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->get('session');
        $session->getFlashBag()->set('sonata_flash_success', 'translations.cache_removed');

        return $this->redirect($this->admin->generateUrl('list'));
    }
}
 