<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Admin\Document;

use Ibrows\SonataTranslationBundle\Admin\TranslationAdmin as BaseTranslationAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

/**
 * Class MongoDBTranslationAdmin
 * @package Networking\InitCmsBundle\Admin\Document
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MongoDBTranslationAdmin extends BaseTranslationAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('key', 'doctrine_mongodb_string')
            ->add('domain', 'doctrine_mongodb_string');
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('key', 'string')
            ->add('domain', 'string')
        ;

        foreach ($this->managedLocales as $locale) {
            $fieldDescription = $this->modelManager->getNewFieldDescriptionInstance($this->getClass(), $locale);
            $fieldDescription->setTemplate('IbrowsSonataTranslationBundle:CRUD:base_inline_translation_field.html.twig');
            $fieldDescription->setOption('locale', $locale);
            $fieldDescription->setOption('editable', $this->editableOptions);
            $list->add($fieldDescription);
        }
    }

    /**
     * @param unknown $name
     * @return multitype:|NULL
     */
    public function getTemplate($name)
    {
        if ($name === 'layout') {
            return 'IbrowsSonataTranslationBundle::translation_layout.html.twig';
        }

        if ($name === 'list') {
            return 'NetworkingInitCmsBundle:TranslationAdmin:list.html.twig';
        }

        return parent::getTemplate($name);
    }

}
