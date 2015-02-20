<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Block;

use Doctrine\ORM\Query;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class PagesBlockService
 * @package Networking\InitCmsBundle\Block
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
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
        $pages = $this->em->getAllSortBy('updatedAt', 'DESC', Query::HYDRATE_ARRAY);

        $draftPageCount = 0;
        $reviewPageCount = 0;
        $publishedPageCount = 0;
        $reviewPages = array();
        $draftPages = array();


        foreach ($pages as $page) {
            if (array_key_exists('snapshots', $page) && count($page['snapshots']) > 0) {
                $publishedPageCount++;
            }
            if ($page['status'] == PageInterface::STATUS_REVIEW) {
                $reviewPageCount++;
                $draftPageCount++;
                $reviewPages[\Locale::getDisplayLanguage($page['locale'])][] = $page;
            }

            if ($page['status'] == PageInterface::STATUS_DRAFT) {
                $draftPageCount++;
                $draftPages[\Locale::getDisplayLanguage($page['locale'])][] = $page;
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