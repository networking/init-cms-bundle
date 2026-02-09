<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('fos_ck_editor', [
        'default_config' => 'init_cms',
        'configs' => [
            'init_cms' => [
                'extraPlugins' => ['internal_link', 'autoembed', 'autolink', 'textmatch', 'embed', 'embedsemantic', 'embedbase'],
                'filebrowserBrowseRoute' => 'admin_networking_initcms_media_init_ckeditor_browser',
                'filebrowserBrowseRouteParameters' => [
                    'provider' => 'sonata.media.provider.file',
                    'context' => 'default',
                ],
                'filebrowserImageBrowseRoute' => 'admin_networking_initcms_media_init_ckeditor_browser',
                'filebrowserImageBrowseRouteParameters' => [
                    'provider' => 'sonata.media.provider.image',
                    'context' => 'default',
                ],
                'filebrowserUploadRoute' => 'admin_networking_initcms_media_init_ckeditor_upload_file',
                'filebrowserUploadRouteParameters' => [
                    'provider' => 'sonata.media.provider.file',
                ],
                'filebrowserImageUploadRoute' => 'admin_networking_initcms_media_init_ckeditor_upload_image',
                'filebrowserImageUploadRouteParameters' => [
                    'provider' => 'sonata.media.provider.image',
                    'context' => 'default',
                ],
                'removeButtons' => 'NewPage,Underline,Subscript,Superscript,Styles,PageBreak,Flash,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Print,Preview',
                'toolbar' => 'init_cms',
                'bodyClass' => 'row',
                'emailProtection' => 'encode',
                'templates_replaceContent' => false,
                'height' => '400px',
                'removePlugins' => 'exportpdf',
                'clipboard_handleImages' => false,
                'embed_provider' => '//iframe.ly/api/oembed?url={url}&callback={callback}&api_key=de32581bf823a9b515c422',
            ],
        ],
        'plugins' => [
            'wordcount' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/wordcount/',
                'filename' => 'plugin.js',
            ],
            'ajax' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/ajax/',
                'filename' => 'plugin.js',
            ],
            'xml' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/xml/',
                'filename' => 'plugin.js',
            ],
            'internal_link' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/internal-link/',
                'filename' => 'plugin.js',
            ],
            'autoembed' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/autoembed/',
                'filename' => 'plugin.js',
            ],
            'autolink' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/autolink/',
                'filename' => 'plugin.js',
            ],
            'textmatch' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/textmatch/',
                'filename' => 'plugin.js',
            ],
            'embedbase' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/embedbase/',
                'filename' => 'plugin.js',
            ],
            'embed' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/embed/',
                'filename' => 'plugin.js',
            ],
            'embedsemantic' => [
                'path' => '/bundles/networkinginitcms/js/ckeditor/embedsemantic/',
                'filename' => 'plugin.js',
            ],
        ],
        'toolbars' => [
            'configs' => [
                'init_cms' => ['@clipboard', '@editing', '@document', '/', '@paragraph', '@basicstyles', '@links', '@insert', '@styles', '@colors', '@tools'],
            ],
            'items' => [
                'document' => ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'],
                'clipboard' => ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'],
                'editing' => ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'],
                'forms' => ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
                'basicstyles' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],
                'paragraph' => ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl'],
                'links' => ['Link', 'Unlink', 'Anchor'],
                'insert' => ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe', 'Embed', 'Autoembed'],
                'styles' => ['Styles', 'Format', 'Font', 'FontSize'],
                'colors' => ['TextColor', 'BGColor'],
                'tools' => ['Maximize', 'ShowBlocks'],
                'others' => ['-'],
                'about' => ['About'],
            ],
        ],
    ]);
};
