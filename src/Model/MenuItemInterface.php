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

use Doctrine\Common\Collections\Collection;

/**
 * Class MenuItemInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface MenuItemInterface
{
    public const string PATH_SEPARATOR = '/';

    public const string VISIBILITY_PUBLIC = 'public';

    public const string VISIBILITY_PROTECTED = 'protected';

    public function __toString();

    public function getId(): ?int;

    public function setPage(?PageInterface $page = null): static;

    public function getPage(): ?PageInterface;

    public function setRedirectUrl(?string $redirectUrl = null): static;

    public function getRedirectUrl(): ?string;

    public function setHidden(?bool $hidden = null): static;

    public function getHidden(): ?bool;

    public function isHidden(): bool;

    public function setInternalUrl(?string $route = null): static;

    public function getInternalUrl(): ?string;

    public function setName(?string $name = null): static;

    public function getName(): ?string;

    public function setParent(?MenuItemInterface $parent = null): static;

    public function getParent(): ?MenuItemInterface;

    public function setLft($lft): static;

    public function getLft();

    public function setLvl($lvl): static;

    public function getLvl(): int;

    public function setRgt($rgt): static;

    public function getRgt(): int;

    public function setRoot(int $root): static;

    public function getRoot(): int;

    public function getMenu(): MenuItemInterface;

    public function setIsRoot(bool $isRoot);

    public function getIsRoot(): bool;

    public function getRootParent(MenuItemInterface $menuItem): MenuItemInterface;

    public function getParentByLevel(int $level = 1): ?MenuItemInterface;

    public function setMenu(?MenuItemInterface $menuItem = null): static;

    public function getChildren(): Collection;

    public function getActiveChildren(): Collection;

    public function getChildrenByStatus($status): Collection;

    public function setPath($path): static;

    public function getPath(): ?string;

    public function getRouteId(): ?int;

    public function setLocale(?string $locale = null): static;

    public function getLocale(): ?string;

    public function setDescription(?string $description = null): static;

    public function getDescription(): ?string;

    public function setLinkClass(?string $linkClass = null): static;

    public function getLinkClass(): ?string;

    public function setLinkRel(?string $linkRel = null): static;

    public function getLinkRel(): ?string;

    public function setLinkTarget(?string $linkTarget = null): static;

    public function getLinkTarget(): ?string;

    public function setVisibility(string $visibility): static;

    public function getVisibility(): string;

    public static function getVisibilityList(): array;

    public function getLinkAttributes(): array;

    public function hasChildren(): bool;
}
