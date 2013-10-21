<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Block;

use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\BlockBundle\Block\BaseBlockService,
    Sonata\BlockBundle\Model\BlockInterface,
    Sonata\AdminBundle\Form\FormMapper,
    Sonata\AdminBundle\Validator\ErrorElement,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PagesBlockService extends BaseBlockService
{
    /**
     * @var PageManagerInterface $em
     */
    protected $em;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param PageManagerInterface $em
     */
    public function __construct($name, EngineInterface $templating, PageManagerInterface $em)
    {
        $this->name = $name;
        $this->templating = $templating;
        $this->em = $em;

    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {

        $pages = $this->em->getAllSortBy('updatedAt');

        $draftPageCount = 0;
        $reviewPageCount = 0;
        $publishedPageCount = 0;
        $reviewPages = array();
        $draftPages = array();

        foreach ($pages as $page) {
            /** @var \Networking\InitCmsBundle\Model\PageInterface $page */
            if ($page->hasPublishedVersion()) {
                $publishedPageCount++;
            }
            if ($page->isReview()) {
                $reviewPageCount++;
                $draftPageCount++;
                $reviewPages[\Locale::getDisplayLanguage($page->getLocale())][] = $page;
            }

            if ($page->isDraft()) {
                $draftPageCount++;
                $draftPages[\Locale::getDisplayLanguage($page->getLocale())][] = $page;
            }
        }

        return $this->renderResponse(
            $blockContext->getTemplate(),
            array(
                'block' => $blockContext->getBlock(),
                'draft_pages' => $draftPageCount,
                'review_pages' => $reviewPageCount,
                'published_pages' => $publishedPageCount,
                'pages' => $pages,
                'reviewPages' => $reviewPages,
                'draftPages' => $draftPages
            ),
            $response
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Online Users Block';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('template' => 'NetworkingInitCmsBundle:Block:block_page_status.html.twig'));
    }
}