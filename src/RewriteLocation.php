<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Url\Url;

/**
 * @psalm-immutable
 */
interface RewriteLocation
{
    public function __invoke(Url $location): Url;
}
