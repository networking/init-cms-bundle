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

use Sonata\BlockBundle\Block\BaseBlockService,
    Sonata\BlockBundle\Model\BlockInterface,
    Sonata\AdminBundle\Form\FormMapper,
    Sonata\AdminBundle\Validator\ErrorElement,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
class PagesBlockService extends BaseBlockService
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext $em
     */
    protected $em;

    /**
     * @param string                                                     $name
     *
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     */
    public function __construct($name, EngineInterface $templating, $em)
    {


        $this->name = $name;
        $this->templating = $templating;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $block, Response $response = null)
    {
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $pages = $this->em->getRepository('NetworkingInitCmsBundle:Page')
            ->getAllSortBy('updatedAt');

        $draftPageCount = 0;
        $reviewPageCount = 0;
        $publishedPageCount = 0;
        $reviewPages = array();
        $draftPages = array();

        foreach ($pages as $page) {

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


        switch($settings['type']){
            default:
                $template = 'NetworkingInitCmsBundle:Block:block_page_status.html.twig';

        }

        return $this->renderResponse(
            $template,
            array(
                'block' => $block,
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
    public function getDefaultSettings()
    {
        return array('type' => 'page_status');
    }
}