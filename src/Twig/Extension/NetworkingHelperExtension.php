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

use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Model\ConfigManager;
use JMS\Serializer\SerializerInterface;
use Networking\InitCmsBundle\Admin\Model\LayoutBlockAdmin;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Networking\InitCmsBundle\Form\Type\IconradioType;
use Networking\InitCmsBundle\Helper\BundleGuesser;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Twig\TokenParser\JSTokenParser;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class NetworkingHelperExtension.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class NetworkingHelperExtension extends AbstractExtension
{

    /**
     * @var bool
     */
    protected $captureLock = false;

    /**
     * @var array
     */
    protected $collectedHtml = [];

    /**
     * @var bool
     */
    protected $ckeditorRendered = false;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var Environment
     */
    protected $templating;

    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var LayoutBlockAdmin
     */
    protected $layoutBlockAdmin;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var ConfigManager
     */
    protected $ckEditorConfigManager;
    /**
     * @var array
     */
    protected $templates;

    /**
     * @var array
     */
    protected $contentTypes;

    /**
     * NetworkingHelperExtension constructor.
     *
     * @param KernelInterface      $kernel
     * @param Environment      $templating
     * @param RequestStack         $requestStack
     * @param ManagerRegistry      $doctrine
     * @param TranslatorInterface  $translator
     * @param LayoutBlockAdmin     $layoutBlockAdmin
     * @param SerializerInterface  $serializer
     * @param PageManagerInterface $pageManager
     * @param ConfigManager        $ckEditorConfigManager
     * @param array                $templates
     * @param array                $contentTypes
     */
    public function __construct(
        KernelInterface $kernel,
        Environment $templating,
        RequestStack $requestStack,
        ManagerRegistry $doctrine,
        TranslatorInterface $translator,
        LayoutBlockAdmin $layoutBlockAdmin,
        SerializerInterface $serializer,
        PageManagerInterface $pageManager,
        ConfigManager $ckEditorConfigManager,
        $templates = [],
        $contentTypes = []

    ) {
        $this->kernel = $kernel;
        $this->templating = $templating;
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->layoutBlockAdmin = $layoutBlockAdmin;
        $this->serializer = $serializer;
        $this->pageManager = $pageManager;
        $this->ckEditorConfigManager = $ckEditorConfigManager;
        $this->templates = $templates;
        $this->contentTypes = $contentTypes;
    }

    /**
     * Returns the token parser instance to add to the existing list.
     *
     * @return array An array of Twig_TokenParser instances
     */
    public function getTokenParsers()
    {
        return [
            // {% jsblock %}
            new JSTokenParser(),
        ];
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        $filters = [
            new TwigFilter('truncate', [$this, 'truncate'], ['needs_environment' => true]),
            new TwigFilter('excerpt', [$this, 'excerpt'], ['needs_environment' => true]),
            new TwigFilter('highlight', [$this, 'highlight']),
            new TwigFilter('base64_encode', [$this, 'base64Encode']),
        ];

        return $filters;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('render_initcms_block', [$this, 'renderInitCmsBlock'], ['is_safe' => ['html']]),
            new TwigFunction('get_initcms_template_zones', [$this, 'getInitCmsTemplateZones'], ['is_safe' => ['html']]),
            new TwigFunction('render_initcms_field_as_string', [$this, 'renderInitcmsFieldAsString'], ['is_safe' => ['html']]),
            new TwigFunction('get_form_field_zone', [$this, 'getFormFieldZone'], ['is_safe' => ['html']]),
            new TwigFunction('get_sub_form_by_zone', [$this, 'getSubFormsByZone'], ['is_safe' => ['html']]),
            new TwigFunction('get_content_type_options', [$this, 'getContentTypeOptions'], ['is_safe' => ['html']]),
            new TwigFunction('get_initcms_admin_icon_path', [$this, 'getInitcmsAdminIconPath'], ['is_safe' => ['html']]),
            new TwigFunction('get_current_admin_locale', [$this, 'getCurrentAdminLocale'], ['is_safe' => ['html']]),
            new TwigFunction('render_initcms_admin_block', [$this, 'renderInitcmsAdminBlock'], ['is_safe' => ['html']]),
            new TwigFunction('render_content_type_name', [$this, 'renderContentTypeName'], ['is_safe' => ['html']]),
            new TwigFunction('render_admin_subnav', [$this, 'renderAdminSubNav'], ['is_safe' => ['html']]),
            new TwigFunction('is_admin_active', [$this, 'isAdminActive'], ['is_safe' => ['html']]),
            new TwigFunction('is_admin_group_active', [$this, 'isAdminGroupActive'], ['is_safe' => ['html']]),
            new TwigFunction('get_initcms_page_url', [$this, 'getPageUrl'], ['is_safe' => ['html']]),
            new TwigFunction('get_media_by_id', [$this, 'getMediaById'], ['is_safe' => ['html']]),
            new TwigFunction('ckeditor_is_rendered', [$this, 'ckeditorIsRendered']),
            new TwigFunction('content_css', [$this, 'getContentCss']),
            new TwigFunction('get_file_icon', [$this, 'getFileIcon']),
            new TwigFunction('crop_middle', [$this, 'cropMiddle']),
            new TwigFunction('human_readable_filesize', [$this, 'getHumanReadableSize']),
        ];
    }

    /**
     * @param $template
     * @param LayoutBlockInterface $layoutBlock
     * @param array $params
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderInitCmsBlock($template, LayoutBlockInterface $layoutBlock, $params = [])
    {
        if (!$serializedContent = $layoutBlock->getSnapshotContent()) {
            // Draft View
            $contentItem = $layoutBlock->getContent();
        } else {
            // Live View
            $contentItem = $this->serializer->deserialize(
                $serializedContent,
                $layoutBlock->getClassType(),
                'json'
            );
        }

        if (!is_object($contentItem)) {
            $this->layoutBlockAdmin->delete($layoutBlock);

            return '---';
        }

        $options = $contentItem->getTemplateOptions($params);

        $options = array_merge($options, $params);

        return $this->templating->render($template, $options);
    }

    /**
     * @param LayoutBlockInterface $layoutBlock
     * @return bool|string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderInitcmsAdminBlock(LayoutBlockInterface $layoutBlock)
    {
        if ($layoutBlock->getObjectId()) {
            // Draft View
            $contentItem = $layoutBlock->getContent();
        } else {
            $classType = $layoutBlock->getClassType();
            $contentItem = new $classType();
        }

        if (!is_object($contentItem)) {
            $this->layoutBlockAdmin->delete($layoutBlock);

            return false;
        }

        $adminContent = $contentItem->getAdminContent();

        return $this->templating->render($adminContent['template'], $adminContent['content']);
    }

    /**
     * @param LayoutBlockInterface $layoutBlock
     *
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

        return $this->translator->trans($name);
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param string                                   $adminCode
     *
     * @return bool|\Knp\Menu\ItemInterface
     */
    public function renderAdminSubNav(AdminInterface $admin, $adminCode = '')
    {
        $menu = false;

        if (method_exists($admin, 'getSubNavLinks')) {
            $menu = $admin->getMenuFactory()->createItem('root');
            $menu->setChildrenAttribute('class', 'ul-second-level');

            foreach ($admin->getSubNavLinks() as $label => $link) {
                $active = false;

                if ($link instanceof AdminInterface) {
                    $active = ($link->getCode() == $adminCode);
                    $link = $link->generateUrl('list');
                }

                $menu->addChild(
                    $label,
                    ['uri' => $link, 'attributes' => ['class' => 'second-level']]
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
     * @param string                                   $adminCode
     *
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
     * @param array  $group
     * @param string $adminCode
     *
     * @return bool
     */
    public function isAdminGroupActive(array $group, $adminCode = '')
    {
        $active = false;
        /** @var AdminInterface $admin */
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
     * @param $template
     *
     * @return mixed
     */
    protected function getZonesByTemplate($template)
    {
        $zones = $this->templates[$template]['zones'];

        foreach ($zones as $key => $zone) {
            $temp = array_map([$this, 'jsString'], $zone['restricted_types']);
            $zones[$key]['restricted_types'] = '['.implode(',', $temp).']';

            $zones[$key]['restricted_types'] = json_encode($zone['restricted_types']);
        }

        return $zones;
    }

    /**
     * @return array|mixed
     */
    public function getCurrentTemplate()
    {
        $request = $this->requestStack->getCurrentRequest();
        $pageId = (!$request->get('objectId')) ? $request->get('id') : $request->get('objectId');

        $template = null;

        if ($pageId) {
            $page = $this->pageManager->findById($pageId);
            $template = $page->getTemplateName();
        } else {
            $firstTemplate = reset($this->templates);
            $template = key($firstTemplate);
        }

        if (is_null($template)) {
            return ['Please Select Template first'];
        }

        return $template;
    }

    /**
     * @return mixed
     */
    public function getContentTypeOptions()
    {
        return $this->contentTypes;
    }

    /**
     * Guess which icon should represent an entity admin.
     *
     * @param AdminInterface $admin
     * @param string         $size
     * @param bool           $active
     *
     * @return string
     *
     * @todo This is a bit of a hack. Need to provide a better way of providing admin icons
     */
    public function getInitcmsAdminIconPath(
        AdminInterface $admin,
        $size = 'small',
        $active = false
    ) {
        $state = $active ? '_active' : '';
        $imagePath = '/bundles/networkinginitcms/img/icons/icon_blank_'.$size.$state.'.png';
        $bundleGuesser = new BundleGuesser();
        $bundleGuesser->initialize($admin);
        $bundleName = $bundleGuesser->getBundleShortName();
        $bundles = $this->kernel->getBundle($bundleName, false);
        $bundle = end($bundles);

        $path = $bundle->getPath();

        $slug = self::slugify($admin->getLabel());

        $iconName = 'icon_'.$slug.'_'.$size.$state;

        $folders = ['img/icons', 'image/icons', 'img', 'image'];

        $imageType = ['gif', 'png', 'jpg', 'jpeg'];

        foreach ($folders as $folder) {
            foreach ($imageType as $type) {
                $icon = $folder.DIRECTORY_SEPARATOR.$iconName.'.'.$type;

                if (file_exists(
                    $path.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.$icon
                )
                ) {
                    $imagePath = 'bundles'.DIRECTORY_SEPARATOR.str_replace(
                            'bundle',
                            '',
                            strtolower($bundleName)
                        ).DIRECTORY_SEPARATOR.$icon;
                }
            }
        }

        return $imagePath;
    }

    /**
     * Modifies a string to remove all non ASCII characters and spaces.
     *
     * @param $text
     *
     * @return mixed|string
     */
    public static function slugify($text)
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
     * returns the locale of the current admin user.
     *
     * @param AdminInterface $admin
     *
     * @return mixed|string
     */
    public function getCurrentAdminLocale(\Sonata\AdminBundle\Admin\AdminInterface $admin)
    {
        $locale = '';

        if (!$admin->hasRequest()) {
            $admin->setRequest($this->requestStack->getCurrentRequest());
        }

        if ($subject = $admin->getSubject()) {
            return $this->getFieldValue($subject, 'locale');
        } elseif ($filter = $admin->getDatagrid()->getFilter('locale')) {
            /** @var \Sonata\AdminBundle\Filter\Filter $filter */
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
     * Fetch the variables from the given content type object.
     *
     * @param $object
     * @param $fieldName
     * @param null $method
     *
     * @return mixed|string
     */
    public function getFieldValue($object, $fieldName, $method = null)
    {
        $getters = [];
        // prefer method name given in the code option
        if ($method) {
            $getters[] = $method;
        }

        $camelizedFieldName = self::camelize($fieldName, true);
        $getters[] = 'get'.$camelizedFieldName;
        $getters[] = 'is'.$camelizedFieldName;

        foreach ($getters as $getter) {
            if (method_exists($object, $getter)) {
                return call_user_func([$object, $getter]);
            }
        }

        return '';
    }

    /**
     * Camelize a string.
     *
     * @static
     *
     * @param $str
     * @param bool $firstToCapital
     *
     * @return mixed
     */
    public static function camelize($str, $firstToCapital = false)
    {
        if ($firstToCapital) {
            $str[0] = strtoupper($str[0]);
        }

        return preg_replace_callback('/_([a-z])/', function ($s) {
            return strtoupper($s[1]);
        }, $str);
    }

    /**
     * @return mixed
     */
    private function getCurrentLocale()
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }

    /**
     * @param $class
     *
     * @return string
     */
    public function networking_init_cms_resource_bundle($class)
    {
        return strtolower(str_replace('bundle', '', $this->getBundleName($class)));
    }

    /**
     * @param $class
     *
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
     *
     * @return string
     */
    public function getShortName($class)
    {
        $reflector = new \ReflectionClass(get_class($class));

        return $reflector->getShortName();
    }

    /**
     * @param $class
     *
     * @return mixed
     */
    public function getBundleShortName($class)
    {
        return str_replace('\\', '', $this->getBundleName($class));
    }

    /**
     * Gets a list of forms sorted to a particular zone.
     *
     * @param $formChildren
     * @param $zone
     *
     * @return array
     */
    public function getSubFormsByZone($formChildren, $zone)
    {
        $zones = [];

        foreach ($formChildren as $subForms) {
            if ($this->getFormFieldZone($subForms) == $zone) {
                $zones[] = $subForms;
            }
        }

        return $zones;
    }

    /**
     * @param FormView $formView
     *
     * @return mixed
     */
    public function getFormFieldZone(FormView $formView)
    {
        $zones = $this->getZoneNames();

        /** @var LayoutBlockInterface $layoutBlock */
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
     * @return array
     */
    public function getZoneNames()
    {
        $zones = [];

        $template = $this->getCurrentTemplate();

        foreach ($this->getZonesByTemplate($template) as $zone) {
            $zones[] = $zone['name'];
        }

        return $zones;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function base64Encode($value)
    {
        return base64_encode($value);
    }

    /**
     * @param $template
     * @param $object
     * @param FormView $formView
     * @param null     $translationDomain
     *
     * @return string
     *
     * @throws \Twig\Error\Error
     */
    public function renderInitcmsFieldAsString(
        $template,
        $object,
        FormView $formView,
        $translationDomain = null
    ) {

        /** @var $fieldDescription \Sonata\DoctrineORMAdminBundle\Admin\FieldDescription */
        $fieldDescription = $formView->vars['sonata_admin']['field_description'];
        $value = '';

        switch ($fieldDescription->getType()) {
            case CheckboxType::class:
            case BooleanType::class:
                if ($fieldDescription->getValue($object)) {
                    $value = 'positive';
                } else {
                    $value = 'negative';
                }
                break;
            case ChoiceType::class:
            case AutocompleteType::class:
            case IconradioType::class:
            case EntityType::class:
                $choices = $formView->vars['choices'];
                $preferredChoices = $formView->vars['preferred_choices'];
                $choices = array_merge($choices, $preferredChoices);
                $selected = $fieldDescription->getValue($object);
                if (is_object($selected)) {
                    $selected = (string) $selected->getId();
                }
                foreach ($choices as $choice) {
                    if ($choice->value == $selected) {
                        $value = $choice->label;
                    }
                }
                break;
            case DateType::class:
                /** @var $date \DateTime */
                $date = $fieldDescription->getValue($object);
                if ($date) {
                    $value = $date->format('d.m.Y');
                } else {
                    $value = '';
                }
                break;
            case DateTimeType::class:
                /** @var $date \DateTime */
                $date = $fieldDescription->getValue($object);
                if ($date) {
                    $value = $date->format('d.m.Y H:i');
                } else {
                    $value = '';
                }
                break;

            case TextType::class:
            case ModelHiddenType::class:
            case HiddenType::class:
            default:
                $value = $fieldDescription->getValue($object);
                break;
        }

        if ($displayMethod = $fieldDescription->getOption('display_method')) {
            $value = $this->getFieldValue($object, $fieldDescription->getFieldName(), $displayMethod);
        }

        $valueTranslationDomain = $fieldDescription->getTranslationDomain();

        if ('visibility' === $fieldDescription->getName() || 'templateName' === $fieldDescription->getName()) {
            if (array_key_exists('choice_translation_domain', $formView->vars)) {
                $valueTranslationDomain = $formView->vars['choice_translation_domain'];
            }

            $value = $this->translator->trans($value, [], $valueTranslationDomain);
        }

        $options = [
            'page' => $object,
            'field' => $fieldDescription->getName(),
            'value' => $value,
            'translation_domain' => $translationDomain,
        ];

        return $this->templating->render($template, $options);
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
        return implode("\n    ", $this->collectedHtml)."\n";
    }

    /**
     * Extracts an excerpt from the text surrounding the phrase with a number of characters on each side
     * determined by radius.
     *
     * @param Environment $env
     * @param string            $text     String to search the phrase in
     * @param string            $phrase   Phrase that will be searched for
     * @param int               $radius   The amount of characters that will be returned on each side of the founded phrase
     * @param string            $ellipsis Ending that will be appended
     *
     * @return string
     */
    public function excerpt(Environment $env, $text, $phrase, $radius = 100, $ellipsis = '...')
    {
        if (empty($text) || empty($phrase)) {
            return $this->truncate($env, $text, $radius * 2, $ellipsis);
        }

        $text = html_entity_decode($text, null, $env->getCharset());

        $append = $prepend = $ellipsis;

        $phraseLen = mb_strlen($phrase);
        $textLen = mb_strlen($text);

        $pos = mb_strpos(mb_strtolower($text, $env->getCharset()), mb_strtolower($phrase, $env->getCharset()));

        if ($pos === false) {
            return mb_substr($text, 0, $radius, $env->getCharset()).$ellipsis;
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
        $excerpt = $prepend.$excerpt.$append;

        return $excerpt;
    }

    /**
     * Cuts a string to the length of $length and replaces the last characters
     * with the ellipsis if the text is longer than length.
     *
     * @param \Twig_Environment $env
     * @param string            $text     String to truncate
     * @param int               $length   Length of returned string, including ellipsis
     * @param string            $ellipsis Will be used as Ending and appended to the trimmed string (`ending` is deprecated)
     * @param bool              $exact    If false, $text will not be cut mid-word
     * @param bool              $html     If true, HTML tags would be handled correctly
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
        $openTags = [];
        if ($html) {
            $text = html_entity_decode($text, null, $env->getCharset());
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen(strip_tags($ellipsis));

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
                                --$left;
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
                $truncate .= '</'.$tag.'>';
            }
        }

        return $truncate;
    }

    /**
     * Highlights a given phrase in a text. You can specify any expression in highlighter that
     * may include the \1 expression to include the $phrase found.
     *
     * @param string $text   Text to search the phrase in
     * @param string $phrase The phrase that will be searched
     * @param string $format The piece of html with that the phrase will be highlighted
     * @param bool   $html   If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
     * @param string $regex  a custom regex rule that is used to match words, default is '|$tag|iu'
     *
     * @return mixed
     */
    public function highlight(
        $text,
        $phrase,
        $format = '<span class="highlight">\1</span>',
        $html = false,
        $regex = '|%s|iu'
    ) {
        if (empty($phrase)) {
            return $text;
        }

        if (is_array($phrase)) {
            $replace = [];
            $with = [];

            foreach ($phrase as $key => $segment) {
                $segment = '('.preg_quote($segment, '|').')';
                if ($html) {
                    $segment = "(?![^<]+>)$segment(?![^<]+>)";
                }

                $with[] = (is_array($format)) ? $format[$key] : $format;
                $replace[] = sprintf($regex, $segment);
            }

            return preg_replace($replace, $with, $text);
        }

        $phrase = '('.preg_quote($phrase, '|').')';
        if ($html) {
            $phrase = "(?![^<]+>)$phrase(?![^<]+>)";
        }

        return preg_replace(sprintf($regex, $phrase), $format, $text);
    }

    /**
     * @param PageInterface $page
     *
     * @return mixed
     */
    public function getPageUrl(PageInterface $page)
    {
        return $this->requestStack->getCurrentRequest()->getBaseUrl().$page->getFullPath();
    }

    /**
     * Return a media object by its' id.
     *
     * @param $id
     *
     * @return mixed
     */
    public function getMediaById($id)
    {
        /** @var EntityRepository $repo */
        $repo = $this->doctrine->getRepository('NetworkingInitCmsBundle:Media');

        return $repo->find($id);
    }

    /**
     * Verify if the ckeditor has already been rendered on a page or not.
     *
     * @return bool
     */
    public function ckeditorIsRendered()
    {
        if ($this->ckeditorRendered) {
            return true;
        } else {
            $this->ckeditorRendered = true;

            return false;
        }
    }

    /**
     * Return the path to the content css for the default or named ckeditor config contentsCss.
     *
     * @param null $configName
     *
     * @return bool
     */
    public function getContentCss($configName = null)
    {
        if (is_null($configName)) {
            $configName = $this->ckEditorConfigManager->getDefaultConfig();
        }

        $configs = $this->ckEditorConfigManager->getConfigs();
        if (array_key_exists('contentsCss', $configs[$configName])) {
            $contentCss = $configs[$configName]['contentsCss'];
            if(!is_array($contentCss)){
                $contentCss = [$contentCss];
            }
            return $contentCss;
        }

        return false;
    }

    /**
     * Guess which fontawesome icon to use.
     *
     * @param $filename
     *
     * @return string
     */
    public function getFileIcon($filename)
    {
        $parts = explode('.', $filename);
        $postfix = strtolower(end($parts));
        switch ($postfix) {
            case 'doc':
            case 'docx':
                $icon = 'far fa-file-word';
                break;
            case 'pdf':
                $icon = 'far fa-file-pdf';
                break;
            case 'xls':
            case 'xlsx':
                $icon = 'far fa-file-excel';
                break;
            case 'ppt':
            case 'pptx':
                $icon = 'far fa-file-powerpoint';
                break;
            case 'zip':
                $icon = 'far fa-file-archive';
                break;
            case 'txt':
            case 'rtf':
                $icon = 'far fa-file-alt';
                break;
            case 'png':
            case 'gif':
            case 'jpeg':
            case 'jpg':
            case 'svg':
                $icon = 'far fa-file-image';
                break;
            default:
                $icon = 'far fa-file';
                break;

        }

        return 'fa '.$icon;
    }

    /**
     * @param $text
     * @param $maxLength
     * @param string $delimiter
     *
     * @return string
     */
    public function cropMiddle($text, $maxLength, $delimiter = '...')
    {

        // return text if it doesn't need to be cropped
        if (!$text || strlen($text) <= $maxLength || !substr($text, 0)) {
            return $text;
        }

        $substrLength = floor(($maxLength - strlen($delimiter)) / 2);

        return substr($text, 0, $substrLength).$delimiter.substr($text, -$substrLength);
    }

    /**
     * @param $size
     * @param null $unit
     * @param int  $decemals
     *
     * @return string
     */
    public function getHumanReadableSize($size, $unit = null, $decemals = 2)
    {
        $byteUnits = [' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];
        if (!is_null($unit) && !in_array($unit, $byteUnits)) {
            $unit = null;
        }
        $extent = 1;
        foreach ($byteUnits as $rank) {
            if ((is_null($unit) && ($size < $extent <<= 10)) || ($rank == $unit)) {
                break;
            }
        }

        return number_format($size / ($extent >> 10), $decemals).$rank;
    }

    /**
     * @param $s
     *
     * @return string
     */
    protected function jsString($s)
    {
        return '"'.addcslashes($s, "\0..\37\"\\").'"';
    }
}
