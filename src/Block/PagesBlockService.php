<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Block;

use Doctrine\ORM\Query;
use Networking\InitCmsBundle\Entity\PageSnapshot;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class PagesBlockService.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PagesBlockService extends AbstractBlockService
{
    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    public function __construct(Environment $twig, PageManagerInterface $pageManager)
    {
        $this->pageManager = $pageManager;
        parent::__construct($twig);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $pages = $this->pageManager->getAllSortBy('updatedAt', 'DESC', Query::HYDRATE_ARRAY);

        $drafts = $this->getAllDraftsPages();
        $reviews = $this->getAllDraftsPages();

        $draftPageCount = 0;
        $reviewPageCount = 0;
        $publishedPageCount = $this->getPublishedCount();
        $reviewPages = [];
        $draftPages = [];

        foreach ($drafts as $page) {

            if ($page->isDraft()) {
                $draftPageCount++;
                $draftPages[\Locale::getDisplayLanguage($page->getLocale())][]
                    = $page;
            }
        }

        foreach ($reviews as $page) {

            if ($page->isReview()) {
                $reviewPageCount++;
                $draftPageCount++;

            }
        }


        return $this->renderResponse(
            $blockContext->getTemplate(),
            [
                'block' => $blockContext->getBlock(),
                'draft_pages' => $draftPageCount,
                'review_pages' => $reviewPageCount,
                'published_pages' => $publishedPageCount,
                'reviewPages' => $reviewPages,
                'draftPages' => $draftPages,
            ],
            $response
        );
    }


    public function getPublishedCount(){

        $qb = $this->pageManager->createQueryBuilder('p');
        return $qb->getEntityManager()->getConnection()->executeQuery('SELECT DISTINCT page_id FROM page_snapshot')->rowCount();
    }


    public function getAllDraftsPages()
    {
        $qb = $this->pageManager->createQueryBuilder('p');
        $qb->select('p')
            ->where('p.status = :draft')
            ->orderBy('p.updatedAt', 'DESC');


        return $qb->getQuery()->execute(
            [':draft' => PageInterface::STATUS_DRAFT]
        );
    }

    public function getAllReviewPages()
    {
        $qb = $this->pageManager->createQueryBuilder('p');
        $qb->select('p')
            ->where('p.status = :review')
            ->orderBy('p.updatedAt', 'DESC');


        return $qb->getQuery()->execute(
            [':draft' => PageInterface::STATUS_REVIEW]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Page status block';
    }


    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['template' => '@NetworkingInitCms/Block/block_page_status.html.twig']);
    }
}
