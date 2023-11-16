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

namespace Networking\InitCmsBundle\Model;

use Doctrine\Common\Collections\Collection;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;

/**
 * Class PageInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageInterface extends RouteReferrersReadInterface, IgnoreRevertInterface, VersionableInterface
{
    public const PATH_SEPARATOR = '/';

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PROTECTED = 'protected';

    public static function getStatusList(): array;

    public static function getVisibilityList(): array;

    public function setUpdatedAt(): self;

    /**
     * @return int|string|null
     */
    public function getId();

    public function setCreatedAt(\DateTimeInterface $createdAt): self;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function getTitle(): ?string;

    public function setPageName(?string $title): self;

    public function getPageName(): ?string;

    public function setMetaTitle(?string $title): self;

    public function getMetaTitle(): ?string;

    public function setPath(?string $path): self;

    public function getPath(): ?string;

    public function getLevel(): ?int;

    public function setMetaKeyword(?string $metaKeyword): void;

    public function getMetaKeyword(): ?string;

    public function setMetaDescription(?string $metaDescription): void;

    public function getMetaDescription(): ?string;

    /**
     * @return $this
     */
    public function setParent(PageInterface $parent = null);

    public function getParent(int $level = -1): PageInterface|int|null;

    public function setAlias(?PageInterface $alias);

    public function getAlias(): ?PageInterface;

    public function setParents(array $parents): self;

    /**
     * @return array<int, PageInterface>
     */
    public function getParents();

    public function addChildren(PageInterface $children): self;

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection|array;

    public function setChildren(array $children): self;

    public function getAllChildren(array &$children = []): array;

    public function setAllChildren(array $children): self;

    public function setStatus(?string $status): self;

    public function getStatus(): string;

    public function setVisibility(string $visibility): self;

    public function getVisibility(): string;

    public function isDraft(): bool;

    public function isReview(): bool;

    public function isPublished(): bool;

    public function isOffline(): bool;

    public function isActive(): bool;

    public function setActiveTo(?\DateTimeInterface $activeTo): self;

    public function getActiveTo(): ?\DateTimeInterface;

    public function setActiveFrom(?\DateTimeInterface $activeFrom): self;

    public function getActiveFrom(): ?\DateTimeInterface;

    public function addLayoutBlock(LayoutBlockInterface $layoutBlock): self;

    public function removeLayoutBlock(LayoutBlockInterface $layoutBlock): self;

    public function setLayoutBlocks($layoutBlocks): self;

    public function orderLayoutBlocks(): void;

    /**
     * @return Collection<int, LayoutBlockInterface>
     */
    public function getLayoutBlocks(?string $zone): Collection|array;

    public function setMenuItem(MenuItemInterface $menuItem): self;

    public function removeMenuItem(MenuItemInterface $menuItem): self;

    public function getMenuItem(): Collection;

    public function getMenuItemByRoot($rootId): Collection;

    public function __toString(): string;

    public function setIsHome(bool $isHome): self;

    public function getIsHome(): bool;

    public function isHome(): bool;

    public function setLocale(?string $locale): self;

    public function getLocale(): ?string;

    public function setOriginals(array $originals): self;

    public function getOriginals(): Collection|array;

    public function isDirectTranslation(PageInterface $page): bool;

    public function getDirectTranslationFor(PageInterface $page): ?PageInterface;

    public function addTranslation(PageInterface $page): self;

    public function removeTranslation(PageInterface $page): self;

    public function setTranslations(array|Collection $translations): self;

    /**
     * @return Collection<int, PageInterface>
     */
    public function getTranslations(): Collection|array;

    public function getTranslatedLocales(): array;

    public function getAdminTitle(): string;

    public function setUrl(?string $url);

    public function getUrl(): ?string;

    public function setContentRoute(ContentRouteInterface $contentRoute): self;

    public function getContentRoute(): ContentRouteInterface;

    public function setSnapshots($snapshots): self;

    /**
     * @return Collection<int, PageSnapshotInterface>
     */
    public function getSnapshots(): Collection;

    public function getRoute(): RouteObjectInterface;

    public function setTemplate(string $template): self;

    public function getTemplate(): ?string;

    public function setTemplateName(string $templateName): self;

    public function getTemplateName(): ?string;

    public function getFullPath(): ?string;

    public function getAliasFullPath(): ?string;

    /**
     * @return Collection<PageInterface>
     */
    public function getAllTranslations(): Collection;

    public function buildAllTranslations(array &$translationsArray): void;

    public function hasPublishedVersion(): bool;

    public function convertParentToInteger(): ?int;

    public function convertAliasToInteger(): ?int;

    /**
     * @return array<int>
     */
    public function convertParentsToArray(): array;

    /**
     * @return array<int>
     */
    public function convertChildrenToIntegerArray(): array;

    /**
     * @return array<int>
     */
    public function convertTranslationsToArray(): array;

    /**
     * @return array<int>
     */
    public function convertOriginalsToArray(): array;

    public function getStatusLabel(): string;

    public function setSocialMediaImage(?MediaInterface $socialMediaImage): self;

    public function getSocialMediaImage(): ?MediaInterface;

    public function restoreFromPublished(PageInterface $publishedPage): void;
}
