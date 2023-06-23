<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 19.02.18
 * Time: 16:54.
 */
namespace Networking\InitCmsBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CmsMenuVoter implements VoterInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * CmsMenuVoter constructor.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param ItemInterface $item
     *
     * @return bool|null
     */
    public function matchItem(ItemInterface $item): ?bool
    {
        foreach ($this->currentUriWithAndWithoutSlash() as $path) {
            if (null === $path || null === $item->getUri()) {
                return null;
            }

            if ($item->getUri() === $path) {
                return true;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function currentUriWithAndWithoutSlash()
    {
        $request = $this->requestStack->getCurrentRequest();

        $withSlash = !str_ends_with($request->getPathInfo(), '/') ? $request->getPathInfo().'/' : $request->getPathInfo();
        $withOutSlash = str_ends_with($request->getPathInfo(), '/') ? substr($request->getPathInfo(), 0, -1) : $request->getPathInfo();

        $withSlashWithLocale = '/'.substr($request->getLocale(), 0, 2).$withSlash;
        $withOutSlashWithLocale = '/'.substr($request->getLocale(), 0, 2).$withOutSlash;

        return [
            $request->getBaseUrl().$withSlash,
            $request->getBaseUrl().$withOutSlash,
            $request->getBaseUrl().$withSlashWithLocale,
            $request->getBaseUrl().$withOutSlashWithLocale,
            ];
    }
}
