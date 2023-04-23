<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Url\Url;

interface RewriteLocation
{
    public function __invoke(Url $location): Url;
}
