<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Networking\InitCmsBundle\Model\Tag;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
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
class TagAdmin extends AbstractAdmin
{

    protected $formOptions =['layout' => 'horizontal'];

    public function configureDefaultSortValues(array &$sortValues) : void{
        $sortValues[DatagridInterface::PER_PAGE] = 1000000;
        $sortValues[DatagridInterface::SORT_BY] = 'path';
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
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
    protected function configureFormFields(FormMapper $form): void
    {


        $id = $this->getSubject() ? $this->getSubject()->getId() : null;
        $form
            ->with('tag.form.group_tag', ['label' => false])
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
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('path', null, ['label' => 'filter.label_name']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
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

    public function getTagTree($selected)
    {
        $tags =  $this->getModelManager()->findBy($this->getClass(), ['level' => 1], ['path' => 'ASC']);

        $tagArray = [
            [
                'text' =>  $this->getTranslator()->trans('show_all_media', [], $this->getTranslationDomain()),
                'a_attr' => ['class' => 'show_all_media'],
                'data' => ['show_first' => true, 'id' => 0],
                'children' =>  [],
                'state' => ['selected' => $selected?false:true],
            ],
        ];

        return $this->addTags($tags, $selected, $tagArray);

    }

    /**
     * @param Tag[] $tags
     * @param $selected
     * @param $tagArray
     */
    private function addTags($tags, $selected, $tagArray){
        foreach ( $tags as $tag){
            $item = [
                'text' =>  $tag->getName(),
                'li_attr' => [
                    'id' => 'li_tag_'.$tag->getId(),
                    'class' => sprintf('sortable-tag %s',(count($tag->getChildren())?' has-children':'')),
                    'data-tag-name' => $tag->getName(),
                ],
                'a_attr' => [
                    'class' => 'tag_link',
                    'id' => 'tag_link_'.$tag->getId(),
                    'data-pk' => $tag->getId(),
                    'ondrop' => sprintf("dropTag(event, '%d')", $tag->getId()),
                    "ondragover"=> sprintf("overList(event, '%d')", $tag->getId()),
                    "ondragleave"=> sprintf("exitList(event, '%d')", $tag->getId()),
                ],
                'data' => [
                    'id' => $tag->getId(),
                    'delete_link' => $this->generateObjectUrl('delete', $tag, ['returnToMedia' => true])
                ],
                'state' => [
                    'selected' => $selected === $tag->getId(),
                    'opened' => $tag->hasChild($selected)
                ],
            ];
            if($tag->getChildren()){
                $item['children'] = $this->addTags($tag->getChildren(), $selected, []);
            }

            $tagArray[] = $item;
        }

        return $tagArray;
    }
}
