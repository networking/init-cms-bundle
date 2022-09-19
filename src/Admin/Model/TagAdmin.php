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

use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * Class TagAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class TagAdmin extends AbstractAdmin
{
    /**
     * Default values to the datagrid.
     *
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 25,
        '_sort_by' => 'path',
        '_sort_order' => 'ASC',
    ];

    protected $formOptions =['layout' => 'horizontal'];

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-tags';
    }

    /**
     * @param RouteCollection $collection
     */
    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);

        $collection->add(
            'update_tree',
            'update_tree',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\TagAdminController::updateTreeAction',
            ]
        );

        $collection->add(
            'inline_edit',
            'inline_edit',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\TagAdminController::inlineEditAction',
            ]
        );

        $collection->add(
            'search_tags',
            'search_tags',
            [
                '_controller' => 'Networking\InitCmsBundle\Controller\TagAdminController::searchTagsAction',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {


        $id = $this->getSubject() ? $this->getSubject()->getId() : null;
        $formMapper
            ->add(
                'name',
                null,
                [
                    'attr' => ['class' => 'tag_name_input'],
                    'layout' => $this->getRequest()->isXmlHttpRequest() ? 'horizontal' : 'inline',
                ]
            )
            ->add(
                'parent',
                AutocompleteType::class,
                [
                    'help_block' => 'parent.helper_text',
                    'attr' => ['style' => 'width:220px'],
                    'choice_label' => 'AdminTitle',
                    'class' => $this->getClass(),
                    'layout' => $this->getRequest()->isXmlHttpRequest() ? 'horizontal' : 'inline',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) use ($id) {
                        $qb = $er->createQueryBuilder('t');
                        $qb->orderBy('t.path', 'asc');
                        if ($id) {
                            $qb->where('t.id != :id')
                                ->setParameter(':id', $id);
                        }

                        return $qb;
                    },
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('path', null, ['label' => 'filter.label_name']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('name')
            ->add('path')
            ->add(
                '_action',
                'actions',
                [
                    'label' => ' ',
                    'actions' => [
                        'edit' => [],
                        'delete' => [],
                    ],
                ]
            );
    }
}
