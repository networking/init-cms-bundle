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

use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Twig\TokenParser\JSTokenParser;
use Networking\InitCmsBundle\Helper\ContentInterfaceHelper;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author net working AG <info@networking.ch>
 */
class NetworkingHelperExtension extends \Twig_Extension
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
     * Initialize networking cms helper
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'networking_init_cms_block' => new \Twig_Function_Method($this, 'cmsBlock', array('is_safe' => array('html'))),
            'networking_init_cms_get_template_zones' => new \Twig_Function_Method($this, 'getTemplateZones', array('is_safe' => array('html'))),
            'networking_init_cms_get_field_to_string' => new \Twig_Function_Method($this, 'getFieldToString', array('is_safe' => array('html'))),
            'networking_init_cms_get_field_zone' => new \Twig_Function_Method($this, 'getFieldZone', array('is_safe' => array('html'))),
            'networking_init_cms_sort_form_children' => new \Twig_Function_Method($this, 'sortFormChildren', array('is_safe' => array('html'))),
            'networking_init_cms_content_select' => new \Twig_Function_Method($this, 'contentSelect', array('is_safe' => array('html'))),
            'networking_init_cms_get_admin_icon' => new \Twig_Function_Method($this, 'getAdminIcon', array('is_safe' => array('html'))),
            'networking_init_cms_get_current_admin_locale' => new \Twig_Function_Method($this, 'getCurrentAdminLocale', array('is_safe' => array('html'))),
            'networking_admin_cms_block' => new \Twig_Function_Method($this, 'adminCmsBlock', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns an HTML block for output in the frontend
     *
     * @param $template
     * @param $class
     * @param $id
     * @return mixed
     */
    public function cmsBlock($template, LayoutBlock $layoutBlock)
    {
        if (!$serializedContent = $layoutBlock->getSnapshotContent()) {
            // Draft View
            $contentItem = $this->getService('doctrine')->getRepository($layoutBlock->getClassType())->find($layoutBlock->getObjectId());

        } else {
            // Live View
            $contentItem = $this->getService('serializer')->deserialize($serializedContent, $layoutBlock->getClassType(), 'json');
        }

        $options = $contentItem->getTemplateOptions();


        return $this->getService('templating')->render($template, $options);
    }

    /**
     * Returns an HTML block for output in the admin area
     *
     * @param $class
     * @param $id
     * @return mixed
     */
    public function adminCmsBlock(LayoutBlock $content)
    {

        if ($content->getObjectId()) {
            $contentItem = $this->getService('doctrine')->getRepository($content->getClassType())->find($content->getObjectId());
        } else {

            $classType = $content->getClassType();

            $contentItem = new $classType();
        }

        $adminContent = $contentItem->getAdminContent();

        return $this->getService('templating')->render($adminContent['template'], $adminContent['content']);
    }

    /**
     * @return mixed
     */
    public function getTemplateZones()
    {
        return $this->getZonesByTemplate($this->getCurrentTemplate());
    }

    /**
     * @return mixed
     */
    public function contentSelect()
    {
        return $this->container->getParameter('networking_init_cms.page.content_types');
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
    public function getAdminIcon(\Sonata\AdminBundle\Admin\AdminInterface $admin, $size = 'small', $active = false)
    {
        $state = $active ? '_active' : '';
        $imagePath = '/bundles/networkinginitcms/img/icons/icon_blank_' . $size . $state . '.png';

        $bundleGuesser = $this->container->get('networking_init_cms.helper.bundle_guesser');
        $bundleGuesser->initialize($admin);
        $bundle = $bundleGuesser->getBundleShortName();
        $path = $this->container->get('kernel')->getBundle($bundle)->getPath();

        $iconName = 'icon_' . strtolower($admin->getLabel()) . '_' . $size . $state;

        $folders = array('img/icons', 'image/icons', 'img', 'image');

        $imageType = array('gif', 'png', 'jpg', 'jpeg');

        foreach ($folders as $folder) {
            foreach ($imageType as $type) {
                $icon = $folder . DIRECTORY_SEPARATOR . $iconName . '.' . $type;
                if (file_exists($path . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $icon)) {
                    $imagePath = 'bundles' . DIRECTORY_SEPARATOR . str_replace('bundle', '', strtolower($bundle)) . DIRECTORY_SEPARATOR . $icon;
                }
            }
        }

        return $imagePath;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return mixed|string
     */
    public function getCurrentAdminLocale(\Sonata\AdminBundle\Admin\AdminInterface $admin)
    {
        $locale = '';
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
            } else {
                if (method_exists($admin, 'getDefaultLocale')) {
                    $locale = $admin->getDefaultLocale();
                }
            }
        }

        return $locale;
    }

    /**
     * @return mixed
     */
    private function getCurrentLocale()
    {
        return $this->container->get('request')->getLocale();
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
     *
     * @return string
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
        $request = $this->container->get('request');

        $pageId = (!$request->get('objectId')) ? $request->get('id') : $request->get('objectId');

        $template = null;

        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:Page');

        if ($pageId) {
            $page = $repository->find($pageId);
            $template = $page->getTemplate();
        } else {
            $templates = $this->container->getParameter('networking_init_cms.page.templates');
            $template = key($templates);
        }

        if ($request->getMethod() === 'POST') {
            $uniqid = $request->get('uniqid');
            $postVars = $request->request->get($uniqid);
            $template = $postVars['template'];
        }

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
        $templates = $this->container->getParameter('networking_init_cms.page.templates');

        return $templates[$template]['zones'];
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     * @return mixed
     */
    public function getFieldZone(\Symfony\Component\Form\FormView $formView)
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
     * @param $formChildren
     * @param $zone
     * @return array
     */
    public function sortFormChildren($formChildren, $zone)
    {
        $zones = array();

        foreach ($formChildren as $subForms) {
            if ($this->getFieldZone($subForms) == $zone) {
                $zones[] = $subForms;
            }
        }

        return $zones;
    }

    /**
     * @param $object
     * @param $formView
     */
    public function getFieldToString($template, $object, \Symfony\Component\Form\FormView $formView, $translationDomain = null)
    {

        /** @var $fieldDescription \Sonata\DoctrineORMAdminBundle\Admin\FieldDescription */
        $fieldDescription = $formView->vars['sonata_admin']['field_description'];
        $value = '';


        switch ($fieldDescription->getType()) {
            case 'boolean':
                $value = $fieldDescription->getValue($object);
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
                    if ($choice->isSelected($selected)) {
                        $value = $choice->label;
                    }
                }
                break;
            case 'date':
                /** @var $date  \DateTime */
                $date = $fieldDescription->getValue($object);
                $value = $date->format('d.m.Y');
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
            'value' => $value,
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
        return preg_replace(array('/(^|_| )+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    /**
     * @throws BadFunctionCallException
     */
    public function addToBottom()
    {
        if ($this->captureLock) {
            throw new BadFunctionCallException('Cannot nest onLoad captures');
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
//        return true;
    }

    /**
     * @param $data
     */
    protected function addToCollectedHtml($data)
    {
        $this->collectedHtml[] = $data;
//		return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        return implode("\n    ", $this->collectedHtml) . "\n";
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
}
