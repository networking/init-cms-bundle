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
    public const string PATH_SEPARATOR = '/';

    public const string VISIBILITY_PUBLIC = 'public';

    public const string VISIBILITY_PROTECTED = 'protected';

    public static function getStatusList(): array;

    public static function getVisibilityList(): array;

    public function setUpdatedAt(): static;

    public function getId(): ?int;

    public function setCreatedAt(\DateTimeInterface $createdAt): static;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function getTitle(): ?string;

    public function setPageName(?string $title): static;

    public function getPageName(): ?string;

    public function setMetaTitle(?string $title): static;

    public function getMetaTitle(): ?string;

    public function setPath(?string $path): static;

    public function getPath(): ?string;

    public function getLevel(): ?int;

    public function setMetaKeyword(?string $metaKeyword): void;

    public function getMetaKeyword(): ?string;

    public function setMetaDescription(?string $metaDescription): void;

    public function getMetaDescription(): ?string;

    public function setParent(?PageInterface $parent = null): static;

    public function getParent(int $level = -1): PageInterface|int|null;

    public function setAlias(?PageInterface $alias);

    public function getAlias(): ?PageInterface;

    public function setParents(array $parents): static;

    /**
     * @return Collection<int, static>|array<int, static>
     */
    public function getParents(): Collection|array;

    public function addChildren(PageInterface $children): static;

    /**
     * @return Collection<int, static>|array<int, static>
     */
    public function getChildren(): Collection|array;

    public function setChildren(array $children): static;

    public function getAllChildren(array &$children = []): array;

    public function setAllChildren(array $children): static;

    public function setStatus(?string $status): static;

    public function getStatus(): string;

    public function setVisibility(string $visibility): static;

    public function getVisibility(): string;

    public function isDraft(): bool;

    public function isReview(): bool;

    public function isPublished(): bool;

    public function isOffline(): bool;

    public function isActive(): bool;

    public function setActiveTo(?\DateTimeInterface $activeTo): static;

    public function getActiveTo(): ?\DateTimeInterface;

    public function setActiveFrom(?\DateTimeInterface $activeFrom): static;

    public function getActiveFrom(): ?\DateTimeInterface;

    public function addLayoutBlock(LayoutBlockInterface $layoutBlock): static;

    public function removeLayoutBlock(LayoutBlockInterface $layoutBlock): static;

    public function setLayoutBlocks($layoutBlocks): static;

    public function orderLayoutBlocks(): void;

    /**
     * @return Collection<int, LayoutBlockInterface>|array<int, LayoutBlockInterface>
     */
    public function getLayoutBlocks(?string $zone): Collection|array;

    public function setMenuItem(MenuItemInterface $menuItem): static;

    public function removeMenuItem(MenuItemInterface $menuItem): static;

    public function getMenuItem(): Collection;

    public function getMenuItemByRoot($rootId): Collection;

    public function __toString(): string;

    public function setIsHome(bool $isHome): static;

    public function getIsHome(): bool;

    public function isHome(): bool;

    public function setLocale(?string $locale): static;

    public function getLocale(): ?string;

    public function setOriginals(array $originals): static;

    public function getOriginals(): Collection|array;

    public function isDirectTranslation(PageInterface $page): bool;

    public function getDirectTranslationFor(PageInterface $page): ?PageInterface;

    public function addTranslation(PageInterface $page): static;

    public function removeTranslation(PageInterface $page): static;

    public function setTranslations(array|Collection $translations): static;

    /**
     * @return Collection<int, static>|array<int, static>
     */
    public function getTranslations(): Collection|array;

    public function getTranslatedLocales(): array;

    public function getAdminTitle(): string;

    public function setUrl(?string $url): static;

    public function getUrl(): ?string;

    public function setContentRoute(ContentRouteInterface $contentRoute): static;

    public function getContentRoute(): ContentRouteInterface;

    public function setSnapshots($snapshots): static;

    /**
     * @return Collection<int, PageSnapshotInterface>
     */
    public function getSnapshots(): Collection;

    public function getRoute(): RouteObjectInterface;

    public function setTemplate(string $template): static;

    public function getTemplate(): ?string;

    public function setTemplateName(string $templateName): static;

    public function getTemplateName(): ?string;

    public function getFullPath(): ?string;

    public function getAliasFullPath(): ?string;

    /**
     * @return Collection<int, PageInterface>|array<int, PageInterface>
     */
    public function getAllTranslations(): Collection|array;

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

    public function setSocialMediaImage(?MediaInterface $socialMediaImage): static;

    public function getSocialMediaImage(): ?MediaInterface;

    public function restoreFromPublished(PageInterface $publishedPage): void;
}
