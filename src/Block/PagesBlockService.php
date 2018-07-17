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
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PagesBlockService.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PagesBlockService extends AbstractAdminBlockService
{
    /**
     * @var PageManagerInterface
     */
    protected $em;

    public function setPageManager(PageManagerInterface $em)
    {
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
        $reviewPages = [];
        $draftPages = [];

        foreach ($pages as $page) {
            if (array_key_exists('snapshots', $page) && count($page['snapshots']) > 0) {
                ++$publishedPageCount;
            }
            if ($page['status'] == PageInterface::STATUS_REVIEW) {
                ++$reviewPageCount;
                ++$draftPageCount;
                $reviewPages[\Locale::getDisplayLanguage($page['locale'])][] = $page;
            }

            if ($page['status'] == PageInterface::STATUS_DRAFT) {
                ++$draftPageCount;
                $draftPages[\Locale::getDisplayLanguage($page['locale'])][] = $page;
            }
        }

        return $this->renderResponse(
            $blockContext->getTemplate(),
            [
                'block' => $blockContext->getBlock(),
                'draft_pages' => $draftPageCount,
                'review_pages' => $reviewPageCount,
                'published_pages' => $publishedPageCount,
                'pages' => $pages,
                'reviewPages' => $reviewPages,
                'draftPages' => $draftPages,
            ],
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
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['template' => '@NetworkingInitCms/Block/block_page_status.html.twig']);
    }
}
