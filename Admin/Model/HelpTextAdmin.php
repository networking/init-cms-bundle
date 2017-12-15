<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Admin\Model;

use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class HelpTextAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class HelpTextAdmin extends BaseAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/help';

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-question-sign';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->addIdentifier('title')
            ->addIdentifier('translationKey')
            ->add('locale')
            ->add(
                '_action',
                'actions',
                array(
                    'label' => ' ',
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array()
                    )
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        try {
            $request = $this->getRequest();
        } catch (\RuntimeException $e) {
            $request = $this->getContainer()->get('request');
        }

        $locale = $request->get('locale') ? $request->get('locale') : $request->getLocale();

        $formMapper
            ->add(
                'locale',
                'choice',
                array(
                    'choices' => $this->getLocaleChoices(),
                    'preferred_choices' => array($locale)
                )
            )
            ->add('translationKey')
            ->add('title', null, array('required' => true))
            ->add(
                'text',
                'ckeditor',
                array('config' => array('toolbar' => 'standard', 'contentsCss' => null))
            );

    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'locale',
                'doctrine_orm_callback',
                array(
                    'callback' => array(
                        $this,
                        'getByLocale'
                    ),
                    'hidden' => false
                ),
                'choice',
                array(
                    'empty_value' => false,
                    'choices' => $this->getLocaleChoices(),
                    'preferred_choices' => array($this->getDefaultLocale())
                )

            );

    }

    /**
     * @param \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $queryBuilder
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function getByLocale($queryBuilder, $alias, $field, $data)
    {
        $active = true;
        if (!$locale = $data['value']) {
            $locale = $this->getDefaultLocale();
            $active = false;
        }
        $queryBuilder->andWhere(sprintf('%s.locale = :locale', $alias));
        $queryBuilder->orderBy(sprintf('%s.translationKey', $alias), 'asc');
        $queryBuilder->setParameter(':locale', $locale);

        return $active;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('locale')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->end();
        $errorElement
            ->with('translationKey')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->assertLength(array('max' => 255))
            ->end();
        $errorElement
            ->with('title')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->end();
        $errorElement
            ->with('text')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->end();
        if (strlen(strip_tags($object->getText())) <= 5) {
            $errorElement
                ->with('textMinLength')
                ->addViolation($this->translator->trans('helptext.text.minlength', array(), $this->translationDomain))
                ->end();
        }
    }

}
