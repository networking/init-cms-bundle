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

use Networking\InitCmsBundle\Entity\LayoutBlockFormListener;
use Networking\InitCmsBundle\Form\DataTransformer\PageToIdTransformer;
use Networking\InitCmsBundle\Model\ContentInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * Class LayoutBlockAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LayoutBlockAdmin extends BaseAdmin
{
    /**
     * used to prefix dynamically generated form fields.
     */
    public const CUSTOM_FIELD_PREFIX = 'networking_init_cms_content_';

    public $trackedActions = [];

    protected $pageManager;

    protected $layoutBlockFormListener;
    protected $pageAdmin;

    public function __construct(
        PageManagerInterface $pageManager,
        LayoutBlockFormListener $layoutBlockFormListener,
        PageAdmin $pageAdmin
    ) {
        $this->pageManager = $pageManager;
        $this->layoutBlockFormListener = $layoutBlockFormListener;
        $this->pageAdmin = $pageAdmin;

        parent::__construct();
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false
    ): string {
        return 'cms/layout_block';
    }

    protected function configureRoutes(RouteCollectionInterface $collection
    ): void {
        $collection->add('addBlock', 'add_block', [], ['method' => 'GET']);
        $collection->add('deleteAjax', 'delete_ajax', [], ['method' => 'POST']);
        $collection->add(
            'toggleActive',
            'toggle_active',
            [],
            ['method' => 'POST']
        );
        $collection->add('reload', 'reload', [], ['method' => 'GET']);
        $collection->add(
            'updateFormFieldElement',
            'update_form_fields',
            [],
            ['method' => 'POST']
        );
        $collection->add(
            'updateLayoutBlockSort',
            'update_layout_block_sort',
            [],
            ['method' => 'GET']
        );

        foreach ($collection->getElements() as $key => $element) {
            $collection->get($key)->setOption('expose', true);
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $transformer = new PageToIdTransformer($this->pageManager);
        parent::configureFormFields($form);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('page');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('page')
            ->add('isActive');
    }

    /**
     * @return array
     */
    public function getContentTypes()
    {
        $contentTypes = $this->getContainer()->getParameter(
            'networking_init_cms.page.content_types'
        );

        $choices = [];
        foreach ($contentTypes as $contentType) {
            $choices[$contentType['class']] = $contentType['name'];
        }

        return $choices;
    }

    /**
     * @param mixed $object
     *
     * @return mixed|void
     *
     * @throws ModelManagerException
     */
    public function prePersist(object $object): void
    {
        /* @var ContentInterface $contentObject */
        try {
            $this->autoPageDraft($object->getPage());
        } catch (ModelManagerException) {
            throw new ModelManagerException($this->getTranslator()->trans('Cannot create content, object is invalid', [], 'validators'));
        }
    }

    /**
     * @throws ModelManagerException
     */
    public function preUpdate(object $object): void
    {
        $this->autoPageDraft($object->getPage());
    }

    /**
     * @throws ModelManagerException
     */
    public function preRemove(object $object): void
    {
        $this->autoPageDraft($object->getPage());
    }

    /**
     * @throws \Exception
     */
    public function autoPageDraft(PageInterface $page)
    {
        $page->setStatus(PageInterface::STATUS_DRAFT);
        $page->setUpdatedAt(new \DateTime());
        $this->pageAdmin->update($page);
    }

    public function configureFormOptions(array &$formOptions): void
    {
        $formOptions['csrf_protection'] = false;
        parent::configureFormOptions(
            $formOptions
        ); // TODO: Change the autogenerated stub
    }
}
