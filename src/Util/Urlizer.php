<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Util;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Urlizer
{

    static function urlize(string $text, string $separator = '-'): string
    {
        return new AsciiSlugger()
          ->slug($text, $separator)
          ->lower()
          ->toString();
    }

}