<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Url\Url;

interface LocationRewriter
{
    public function __invoke(Url $location): Url;
}
