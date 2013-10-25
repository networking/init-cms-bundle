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
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;

class HelpTextAdmin extends BaseAdmin
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
        return 'icon-question-sign';
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
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
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
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
            'textarea',
            array(
                'attr' => array(
                    'class' => 'wysiwyg-editor',
                )
            )
        );

    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
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
        // ->add('title', null, array('hidden' => true), null, array())
        //->add('text', null, array(), null, array());

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
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param mixed                                      $object
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
