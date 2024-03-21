<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Model;

use JMS\Serializer\EventDispatcher\Events;
use Networking\InitCmsBundle\Serializer\PageSnapshotDeserializationContext;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

/**
 * Class PageListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class PageListener implements EventSubscriberInterface, PageListenerInterface
{


    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var PageSnapshotManagerInterface
     */
    protected $pageSnapshotManager;

    /**
     * PageListener constructor.
     */
    public function __construct(PageManagerInterface $pageManager, PageSnapshotManagerInterface $pageSnapshotManager)
    {
        $this->pageManager = $pageManager;
        $this->pageSnapshotManager = $pageSnapshotManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_DESERIALIZE,
                'method' => 'onPostDeserialize',
                'format' => 'json',
            ],
            [
                'event' => Events::PRE_DESERIALIZE,
                'method' => 'onPreDeserialize',
                'format' => 'json',
            ],
        ];
    }

    public function onPreDeserialize(\JMS\Serializer\EventDispatcher\PreDeserializeEvent $event)
    {

        $type = $event->getType();

        if('Networking\InitCmsBundle\Entity\LayoutBlock' !== $type['name']){
            return;
        }

        $data = $event->getData();

        if(!is_array($data['snapshot_content'])){
            return;
        }
        $content = '{}';

        if(!empty($data['snapshot_content'])){
            $content = $data['snapshot_content'][0];
        }
        $data['snapshot_content'] = $content;

        $event->setData($data);

    }

    public function onPostDeserialize(\JMS\Serializer\EventDispatcher\ObjectEvent $event)
    {
        /** @var $page PageInterface */
        $page = $event->getObject();

        if ($page instanceof PageInterface) {
            $context = $event->getContext();

            if (!$page->getId()) {
                return;
            }

            if ($parent = $page->getParent()) {
                $parent = $this->pageManager->find($page->getParent());
                $page->setParent($parent);
            } else {
                $page->setParent(null);
            }

            if ($alias = $page->getAlias()) {
                $alias = $this->pageManager->find($page->getAlias());
                $page->setAlias($alias);
            } else {
                $page->setAlias(null);
            }

            if ($parents = $page->getParents()) {
                foreach ($parents as $key => $parent) {
                    if (is_array($parent) && array_key_exists('id', $parent)) {
                        $parent = $parent['id'];
                    }
                    $parents[$key] = $this->pageManager->find($parent);
                }

                $page->setParents($parents);
            } else {
                $page->setParents([]);
            }

            if ($children = $page->getChildren()) {
                foreach ($children as $key => $child) {
                    $children[$key] = $this->pageManager->find($child);
                }

                $page->setChildren($children);
            } else {
                $page->setChildren([]);
            }

            if ($originals = $page->getOriginals()) {
                foreach ($originals as $key => $original) {
                    $originals[$key] = $this->pageManager->find($original);
                }

                $page->setOriginals($originals);
            } else {
                $page->setOriginals([]);
            }
            if ($context instanceof PageSnapshotDeserializationContext && $context->deserializeTranslations()) {
                if ($translations = $page->getTranslations()) {
                    foreach ($translations as $key => $translation) {
                        $translations[$key] = $this->pageManager->find($translation);
                    }
                    $page->setTranslations($translations);
                } else {
                    $originalPageId = $page->getId();
                    $originalPage = $this->pageManager->find($originalPageId);
                    $page->setTranslations($originalPage->getAllTranslations());
                }
            }

            if (!$contentRoute = $page->getContentRoute()->getId()) {
                $lastPageSnapshot = $this->pageSnapshotManager->findLastPageSnapshot($page->getId());

                if ($lastPageSnapshot) {
                    $page->setContentRoute($lastPageSnapshot->getContentRoute());
                }
            }
        }
    }
}
