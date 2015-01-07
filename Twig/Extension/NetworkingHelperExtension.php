<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Twig\Extension;

use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Twig\TokenParser\JSTokenParser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class NetworkingHelperExtension
 * @package Networking\InitCmsBundle\Twig\Extension
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class NetworkingHelperExtension extends \Twig_Extension implements ContainerAwareInterface
{
    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    protected $captureLock = false;

    /**
     * @var array
     */
    protected $collectedHtml = array();

    /**
     * @var bool
     */
    protected $ckeditorRendered = false;


    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'networking_init_cms_helper';
    }


    /**
     * Gets a service.
     *
     * @param string $id The service identifier
     *
     * @return object The associated service
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * Get parameters from the service container
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return array An array of Twig_TokenParser instances
     */
    public function getTokenParsers()
    {
        return array(
            // {% jsblock %}
            new JSTokenParser(),
        );
    }

    public function getFilters()
    {
        $filters = array(
            new \Twig_SimpleFilter('truncate', array($this, 'truncate'), array('needs_environment' => true)),
            new \Twig_SimpleFilter('excerpt', array($this, 'excerpt'), array('needs_environment' => true)),
            new \Twig_SimpleFilter('highlight', array($this, 'highlight'), array('needs_environment' => true)),
            new \Twig_SimpleFilter('base64_encode', array($this, 'base64Encode'), array('needs_environment' => true))
        );


        return $filters;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render_initcms_block', array($this, 'renderInitCmsBlock'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_initcms_template_zones', array($this, 'getInitCmsTemplateZones'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('render_initcms_field_as_string', array($this, 'renderInitcmsFieldAsString'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_form_field_zone', array($this, 'getFormFieldZone'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_sub_form_by_zone', array($this, 'getSubFormsByZone'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_content_type_options', array($this, 'getContentTypeOptions'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_initcms_admin_icon_path', array($this, 'getInitcmsAdminIconPath'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_current_admin_locale', array($this, 'getCurrentAdminLocale'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('render_initcms_admin_block', array($this, 'renderInitcmsAdminBlock'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('render_content_type_name', array($this, 'renderContentTypeName'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('render_admin_subnav', array($this, 'renderAdminSubNav'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('is_admin_active', array($this, 'isAdminActive'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('is_admin_group_active', array($this, 'isAdminGroupActive'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_initcms_page_url', array($this, 'getPageUrl'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('get_media_by_id', array($this, 'getMediaById'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ckeditor_is_rendered', array($this, 'ckeditorIsRendered')),
            new \Twig_SimpleFunction('content_css', array($this, 'getContentCss'))
        );
    }

    /**
     * Returns an HTML block for output in the frontend
     *
     * @param $template
     * @param LayoutBlockInterface $layoutBlock
     * @param array $params
     * @return string
     */
    public function renderInitCmsBlock($template, LayoutBlockInterface $layoutBlock, $params = array())
    {
        /** @var \Sonata\AdminBundle\Admin\AdminInterface $layoutBlockAdmin */
        $layoutBlockAdmin = $this->getService('networking_init_cms.admin.layout_block');
        if (!$serializedContent = $layoutBlock->getSnapshotContent()) {
            // Draft View
            $contentItem = $layoutBlock->getContent();

        } else {
            // Live View
            $contentItem = $this->getService('serializer')->deserialize(
                $serializedContent,
                $layoutBlock->getClassType(),
                'json'
            );
        }

        if (!is_object($contentItem)) {
            $layoutBlockAdmin->delete($layoutBlock);

            return '---';
        }

        $options = $contentItem->getTemplateOptions($params);

        $options = array_merge($options, $params);


        return $this->getService('templating')->render($template, $options);
    }

    /**
     * Returns an HTML block for output in the admin area
     *
     * @param LayoutBlockInterface $layoutBlock
     * @return bool
     */
    public function renderInitcmsAdminBlock(LayoutBlockInterface $layoutBlock)
    {
        /** @var \Sonata\AdminBundle\Admin\AdminInterface $layoutBlockAdmin */
        $layoutBlockAdmin = $this->getService('networking_init_cms.admin.layout_block');
        if ($layoutBlock->getObjectId()) {
            // Draft View
            $contentItem = $layoutBlock->getContent();

        } else {

            $classType = $layoutBlock->getClassType();
            $contentItem = new $classType();
        }

        if (!is_object($contentItem)) {
            $layoutBlockAdmin->delete($layoutBlock);

            return false;
        }

        $adminContent = $contentItem->getAdminContent();

        return $this->getService('templating')->render($adminContent['template'], $adminContent['content']);
    }

    /**
     * @param LayoutBlockInterface $layoutBlock
     * @return mixed
     */
    public function renderContentTypeName(LayoutBlockInterface $layoutBlock)
    {
        if ($layoutBlock->getObjectId()) {
            $contentItem = $layoutBlock->getContent();
        } else {

            $classType = $layoutBlock->getClassType();

            $contentItem = new $classType();
        }

        if (method_exists($contentItem, 'getContentTypeName')) {
            $name = $contentItem->getContentTypeName();
        } else {
            $name = get_class($contentItem);
        }

        return $this->getService('translator')->trans($name);
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param string $adminCode
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function renderAdminSubNav(AdminInterface $admin, $adminCode = '')
    {
        $menu = false;

        if (method_exists($admin, 'getSubNavLinks')) {

            $menu = $admin->getMenuFactory()->createItem('root');
            $request = $this->getService('request');
            $menu->setCurrentUri($request->getRequestUri());
            $menu->setChildrenAttribute('class', 'ul-second-level');

            foreach ($admin->getSubNavLinks() as $label => $link) {
                $active = false;

                if ($link instanceof AdminInterface) {
                    $active = ($link->getCode() == $adminCode);
                    $link = $link->generateUrl('list');
                }

                $menu->addChild(
                    $label,
                    array('uri' => $link, 'attributes' => array('class' => 'second-level'))
                );

                if ($active) {
                    $menu[$label]->setCurrent($active);
                }
            }
        }

        return $menu;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param string $adminCode
     * @return bool
     */
    public function isAdminActive(AdminInterface $admin, $adminCode = '')
    {
        $active = false;
        if ($adminCode == $admin->getCode()) {
            $active = true;
        }

        if (method_exists($admin, 'getSubNavLinks')) {
            foreach ($admin->getSubNavLinks() as $value) {
                if ($value instanceof AdminInterface) {
                    if ($value->getCode() == $adminCode) {
                        $active = true;
                    }
                }
            }
        }

        return $active;
    }

    /**
     * @param array $group
     * @param string $adminCode
     * @return bool
     */
    public function isAdminGroupActive(array $group, $adminCode = '')
    {

        $active = false;

        foreach ($group['items'] as $admin) {
            if ($admin->getCode() == $adminCode) {
                $active = true;
                break;
            }
        }

        return $active;
    }

    /**
     * @return mixed
     */
    public function getInitCmsTemplateZones()
    {
        return $this->getZonesByTemplate($this->getCurrentTemplate());
    }

    /**
     * @return mixed
     */
    public function getContentTypeOptions()
    {
        return $this->getParameter('networking_init_cms.page.content_types');
    }

    /**
     * Guess which icon should represent an entity admin
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param string $size
     * @param bool $active
     * @return string
     *
     * @todo This is a bit of a hack. Need to provide a better way of providing admin icons
     */
    public function getInitcmsAdminIconPath(
        \Sonata\AdminBundle\Admin\AdminInterface $admin,
        $size = 'small',
        $active = false
    ) {
        $state = $active ? '_active' : '';
        $imagePath = '/bundles/networkinginitcms/img/icons/icon_blank_' . $size . $state . '.png';

        $bundleGuesser = $this->getService('networking_init_cms.helper.bundle_guesser');
        $bundleGuesser->initialize($admin);
        $bundleName = $bundleGuesser->getBundleShortName();
        /** @var $kernel \AppKernel */
        $kernel = $this->getService('kernel');
        $bundles = $kernel->getBundle($bundleName, false);
        $bundle = end($bundles);

        $path = $bundle->getPath();

        $slug = self::slugify($admin->getLabel());

        $iconName = 'icon_' . $slug . '_' . $size . $state;

        $folders = array('img/icons', 'image/icons', 'img', 'image');

        $imageType = array('gif', 'png', 'jpg', 'jpeg');

        foreach ($folders as $folder) {
            foreach ($imageType as $type) {
                $icon = $folder . DIRECTORY_SEPARATOR . $iconName . '.' . $type;

                if (file_exists(
                    $path . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $icon
                )
                ) {
                    $imagePath = 'bundles' . DIRECTORY_SEPARATOR . str_replace(
                            'bundle',
                            '',
                            strtolower($bundleName)
                        ) . DIRECTORY_SEPARATOR . $icon;
                }
            }
        }

        return $imagePath;
    }

    /**
     * Modifies a string to remove all non ASCII characters and spaces.
     *
     * @param $text
     * @return mixed|string
     */
    static public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '_', $text);

        // trim
        $text = trim($text, '_');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * returns the locale of the current admin user
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return mixed|string
     */
    public function getCurrentAdminLocale(\Sonata\AdminBundle\Admin\AdminInterface $admin)
    {
        $locale = '';

        if (!$admin->hasRequest()) {
            $admin->setRequest($this->getService('request'));
        }

        if ($subject = $admin->getSubject()) {
            return $this->getFieldValue($subject, 'locale');
        } elseif ($filter = $admin->getDatagrid()->getFilter('locale')) {
            $data = $filter->getValue();
            if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
                $locale = $this->getCurrentLocale();
            }

            $data['value'] = trim($data['value']);

            if (strlen($data['value']) > 0) {
                $locale = $data['value'];
            }

            if (!$locale && method_exists($admin, 'getDefaultLocale')) {
                $locale = $admin->getDefaultLocale();
            }
        }


        return $locale;
    }

    /**
     * @return mixed
     */
    private function getCurrentLocale()
    {
        return $this->getService('request')->getLocale();
    }

    /**
     * @param $class
     * @return string
     */
    public function networking_init_cms_resource_bundle($class)
    {
        return strtolower(str_replace('bundle', '', $this->getBundleName($class)));
    }

    /**
     * @param $class
     * @return string
     */
    public function getBundleName($class)
    {
        $reflector = new \ReflectionClass(get_class($class));

        return ($p1 = strpos($ns = $reflector->getNamespaceName(), '\\')) === false ? $ns :
            substr($ns, 0, ($p2 = strpos($ns, '\\', $p1 + 1)) === false ? strlen($ns) : $p2);
    }

    /**
     * @param $class
     * @return string
     */
    public function getShortName($class)
    {
        $reflector = new \ReflectionClass(get_class($class));

        return $reflector->getShortName();
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getBundleShortName($class)
    {
        return str_replace('\\', '', $this->getBundleName($class));
    }

    /**
     * @return array|mixed
     */
    public function getCurrentTemplate()
    {
        $request = $this->getService('request');
        $pageId = (!$request->get('objectId')) ? $request->get('id') : $request->get('objectId');

        $template = null;


        /** @var $pageManager PageManagerInterface */
        $pageManager = $this->getService('networking_init_cms.page_manager');


        if ($pageId) {
            $page = $pageManager->findById($pageId);
            $template = $page->getTemplateName();
        } else {
            $templates = $this->getParameter('networking_init_cms.page.templates');
            $firstTemplate = reset($templates);
            $template = key($firstTemplate);
        }

//        if ($request->getMethod() === 'POST') {
//            $uniqid = $request->get('uniqid');
//            $postVars = $request->request->get($uniqid);
//            if(array_key_exists('templateName', $postVars))
//            {
//                $template = $postVars['templateName'];
//            }
//        }

        if (is_null($template)) {
            return array('Please Select Template first');
        }

        return $template;
    }

    /**
     * @return array
     */
    public function getZoneNames()
    {
        $zones = array();

        $template = $this->getCurrentTemplate();

        foreach ($this->getZonesByTemplate($template) as $zone) {
            $zones[] = $zone['name'];
        }

        return $zones;
    }

    /**
     * @param $template
     * @return mixed
     */
    protected function getZonesByTemplate($template)
    {
        $templates = $this->getParameter('networking_init_cms.page.templates');

        $zones = $templates[$template]['zones'];

        foreach ($zones as $key => $zone) {
            $temp = array_map(array($this, 'jsString'), $zone['restricted_types']);
            $zones[$key]['restricted_types'] = '[' . implode(',', $temp) . ']';

            $zones[$key]['restricted_types'] = json_encode($zone['restricted_types']);
        }

        return $zones;

    }

    protected function jsString($s)
    {
        return '"' . addcslashes($s, "\0..\37\"\\") . '"';
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     * @return mixed
     */
    public function getFormFieldZone(\Symfony\Component\Form\FormView $formView)
    {
        $zones = $this->getZoneNames();

        if ($layoutBlock = $formView->vars['value']) {

            if ($zone = $layoutBlock->getZone()) {
                if (in_array($zone, $zones)) {
                    return $zone;
                }
            }
        }

        return current($zones);
    }

    /**
     * Gets a list of forms sorted to a particular zone
     *
     * @param $formChildren
     * @param $zone
     * @return array
     */
    public function getSubFormsByZone($formChildren, $zone)
    {
        $zones = array();

        foreach ($formChildren as $subForms) {
            if ($this->getFormFieldZone($subForms) == $zone) {
                $zones[] = $subForms;
            }
        }

        return $zones;
    }

    public function base64Encode($value)
    {
        return base64_encode($value);
    }

    /**
     * @param $template
     * @param $object
     * @param \Symfony\Component\Form\FormView $formView
     * @param null $translationDomain
     * @return mixed
     */
    public function renderInitcmsFieldAsString(
        $template,
        $object,
        \Symfony\Component\Form\FormView $formView,
        $translationDomain = null
    ) {

        /** @var $fieldDescription \Sonata\DoctrineORMAdminBundle\Admin\FieldDescription */
        $fieldDescription = $formView->vars['sonata_admin']['field_description'];
        $value = '';


        switch ($fieldDescription->getType()) {
            case 'hidden':
                $value = $fieldDescription->getValue($object);
                break;
            case 'boolean':
                if ($fieldDescription->getValue($object)) {
                    $value = 'positive';
                } else {
                    $value = 'negative';
                }
                break;
            case 'string':
            case 'text';
                $value = $fieldDescription->getValue($object);
                break;
            case 'choice':
            case 'sonata_type_translatable_choice':
            case 'networking_type_autocomplete':
            case 'networking_type_iconradio':
            case 'entity':
                $choices = $formView->vars['choices'];
                $preferredChoices = $formView->vars['preferred_choices'];
                $choices = array_merge($choices, $preferredChoices);
                $selected = $fieldDescription->getValue($object);
                if (is_object($selected)) {
                    $selected = (string)$selected->getId();
                }

                foreach ($choices as $choice) {
                    /** @var $choice \Symfony\Component\Form\Extension\Core\View\ChoiceView */
                    if ($choice->value == $selected) {
                        $value = $choice->label;
                    }
                }
                break;
            case 'date':
                /** @var $date  \DateTime */
                $date = $fieldDescription->getValue($object);
                $value = $date->format('d.m.Y');
                break;
            case 'datetime':
                /** @var $date  \DateTime */
                $date = $fieldDescription->getValue($object);
                if($date){
                    $value = $date->format('d.m.Y H:i');
                }else{
                    $value = '';
                }
                break;
            case 'sonata_type_model_hidden':
                $value = $fieldDescription->getValue($object);;
                break;
            default:
                var_dump($fieldDescription->getType());
                break;
        }

        if ($displayMethod = $fieldDescription->getOption('display_method')) {
            $value = $this->getFieldValue($object, $fieldDescription->getFieldName(), $displayMethod);
        }

        $options = array(
            'page' => $object,
            'field' => $fieldDescription->getName(),
            'value' => $this->getService('translator')->trans($value, array(), $translationDomain),
            'translation_domain' => $translationDomain
        );

        return $this->getService('templating')->render($template, $options);
    }

    /**
     * Fetch the variables from the given content type object
     *
     * @param  $object
     * @param $fieldName
     * @param  null $method
     * @return mixed
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public function getFieldValue($object, $fieldName, $method = null)
    {

        $getters = array();
        // prefer method name given in the code option
        if ($method) {
            $getters[] = $method;
        }

        $camelizedFieldName = self::camelize($fieldName);
        $getters[] = 'get' . $camelizedFieldName;
        $getters[] = 'is' . $camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return call_user_func(array($object, $getter));
            }
        }

        return '';
    }

    /**
     * Camelize a string
     *
     * @static
     * @param  string $property
     * @return string
     */
    public static function camelize($property)
    {
        return preg_replace(
            array('/(^|_| )+(.)/e', '/\.(.)/e'),
            array("strtoupper('\\2')", "'_'.strtoupper('\\1')"),
            $property
        );
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function addToBottom()
    {
        if ($this->captureLock) {
            throw new \BadFunctionCallException('Cannot nest onLoad captures');
        }

        $this->captureLock = true;
        \ob_start();
    }

    /**
     *
     */
    public function addToBottomEnd()
    {
        $data = \ob_get_clean();
        $this->captureLock = false;
        $this->addToCollectedHtml($data);
    }

    /**
     * @param $data
     */
    protected function addToCollectedHtml($data)
    {
        $this->collectedHtml[] = $data;
    }

    /**
     * @return string
     */
    public function render()
    {
        return implode("\n    ", $this->collectedHtml) . "\n";
    }

    /**
     * Cuts a string to the length of $length and replaces the last characters
     * with the ellipsis if the text is longer than length.
     *
     * @param \Twig_Environment $env
     * @param string $text String to truncate.
     * @param int $length Length of returned string, including ellipsis.
     * @param string $ellipsis Will be used as Ending and appended to the trimmed string (`ending` is deprecated)
     * @param bool $exact If false, $text will not be cut mid-word
     * @param bool $html If true, HTML tags would be handled correctly
     *
     * @return string
     */
    public static function truncate(
        \Twig_Environment $env,
        $text,
        $length = 100,
        $ellipsis = '...',
        $exact = true,
        $html = false
    ) {

        if ($html && $ellipsis == '...' && $env->getCharset() == 'UTF-8') {
            $ellipsis = "\xe2\x80\xa6";
        }

        if (!function_exists('mb_strlen')) {
            class_exists('Multibyte');
        }

        if ($html) {
            $text = html_entity_decode($text, null,  $env->getCharset() );
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen(strip_tags($ellipsis));
            $openTags = array();
            $truncate = '';

            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);

            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);

                    } elseif (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);

                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(
                    preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3])
                );
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all(
                        '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i',
                        $tag[3],
                        $entities,
                        PREG_OFFSET_CAPTURE
                    )
                    ) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length) {
                    break;
                }
            }
        } else {
            if (mb_strlen($text) <= $length) {
                return $text;
            }
            $truncate = mb_substr($text, 0, $length - mb_strlen($ellipsis));
        }
        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if ($html) {
                $truncateCheck = mb_substr($truncate, 0, $spacepos);
                $lastOpenTag = mb_strrpos($truncateCheck, '<');
                $lastCloseTag = mb_strrpos($truncateCheck, '>');
                if ($lastOpenTag > $lastCloseTag) {
                    preg_match_all('/<[\w]+[^>]*>/s', $truncate, $lastTagMatches);
                    $lastTag = array_pop($lastTagMatches[0]);
                    $spacepos = mb_strrpos($truncate, $lastTag) + mb_strlen($lastTag);
                }
                $bits = mb_substr($truncate, $spacepos);
                preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                if (!empty($droppedTags)) {
                    if (!empty($openTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    } else {
                        foreach ($droppedTags as $closingTag) {
                            $openTags[] = $closingTag[1];
                        }
                    }
                }
            }
            $truncate = mb_substr($truncate, 0, $spacepos);
        }
        $truncate .= $ellipsis;

        if ($html) {
            foreach ($openTags as $tag) {

                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }

    /**
     * Extracts an excerpt from the text surrounding the phrase with a number of characters on each side
     * determined by radius.
     *
     * @param \Twig_Environment $env
     * @param string $text String to search the phrase in
     * @param string $phrase Phrase that will be searched for
     * @param integer $radius The amount of characters that will be returned on each side of the founded phrase
     * @param string $ellipsis Ending that will be appended
     * @return string
     */
    public function excerpt(\Twig_Environment $env, $text, $phrase, $radius = 100, $ellipsis = '...')
    {
        if (empty($text) || empty($phrase)) {
            return $this->truncate($env, $text, $radius * 2, $ellipsis);
        }

        $text = html_entity_decode($text, null,  $env->getCharset() );

        $append = $prepend = $ellipsis;

        $phraseLen = mb_strlen($phrase);
        $textLen = mb_strlen($text);

        $pos = mb_strpos(mb_strtolower($text, $env->getCharset()), mb_strtolower($phrase, $env->getCharset()));

        if ($pos === false) {
            return mb_substr($text, 0, $radius, $env->getCharset()) . $ellipsis;
        }

        $startPos = $pos - $radius;
        if ($startPos <= 0) {
            $startPos = 0;
            $prepend = '';
        }

        $endPos = $pos + $phraseLen + $radius;
        if ($endPos >= $textLen) {
            $endPos = $textLen;
            $append = '';
        }

        $excerpt = mb_substr($text, $startPos, $endPos - $startPos, $env->getCharset());
        $excerpt = $prepend . $excerpt . $append;

        return $excerpt;
    }

    /**
     * Highlights a given phrase in a text. You can specify any expression in highlighter that
     * may include the \1 expression to include the $phrase found.
     *
     * @param string $text Text to search the phrase in
     * @param string $phrase The phrase that will be searched
     * @param string $format The piece of html with that the phrase will be highlighted
     * @param bool $html If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
     * @param string $regex a custom regex rule that is used to match words, default is '|$tag|iu'
     *
     * @return mixed
     */
    public function highlight(
        $text,
        $phrase,
        $format = '<span class="highlight">\1</span>',
        $html = false,
        $regex = "|%s|iu"
    ) {
        if (empty($phrase)) {
            return $text;
        }

        if (is_array($phrase)) {
            $replace = array();
            $with = array();

            foreach ($phrase as $key => $segment) {
                $segment = '(' . preg_quote($segment, '|') . ')';
                if ($html) {
                    $segment = "(?![^<]+>)$segment(?![^<]+>)";
                }

                $with[] = (is_array($format)) ? $format[$key] : $format;
                $replace[] = sprintf($regex, $segment);
            }

            return preg_replace($replace, $with, $text);
        }

        $phrase = '(' . preg_quote($phrase, '|') . ')';
        if ($html) {
            $phrase = "(?![^<]+>)$phrase(?![^<]+>)";
        }

        return preg_replace(sprintf($regex, $phrase), $format, $text);
    }

    /**
     * @param PageInterface $page
     * @return mixed
     */
    public function getPageUrl(PageInterface $page)
    {

        $request = $this->getService('request');

        return $request->getBaseUrl().$page->getFullPath();

    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        $db_driver = $this->getParameter('networking_init_cms.db_driver');

        switch ($db_driver) {
            case 'orm':
                return $this->getService('doctrine');
                break;
            case 'mongodb':
                return $this->getService('doctrine_mongodb');
                break;
            default:
                throw new \LogicException('cannot find doctrine for db_driver');
                break;
        }


    }

    /**
     * Return a media object by its' id
     *
     * @param $id
     * @return mixed
     */
    public function getMediaById($id)
    {
        $repo = $this->getService('doctrine')->getRepository('NetworkingInitCmsBundle:Media');

        return $repo->find($id);
    }

    /**
     * Verify if the ckeditor has already been rendered on a page or not
     * @return bool
     */
    public function ckeditorIsRendered()
    {
        if($this->ckeditorRendered){
            return true;
        }else{
            $this->ckeditorRendered = true;
            return false;
        }
    }

    /**
     * Return the path to the content css for the default or named ckeditor config contentsCss
     *
     * @param null $configName
     * @return bool
     */
    public function getContentCss($configName = null)
    {
        /** @var \Ivory\CKEditorBundle\Model\ConfigManager $configManager */
        $configManager = $this->getService('ivory_ck_editor.config_manager');

        if(is_null($configName)){
            $configName = $configManager->getDefaultConfig();
        }

        $configs = $configManager->getConfigs();
        if(array_key_exists('contentsCss', $configs[$configName])){
            return $configs[$configName]['contentsCss'];
        }

        return false;
    }
}


