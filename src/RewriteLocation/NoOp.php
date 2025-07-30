<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\RewriteLocation;

use Innmind\ObjectGraph\RewriteLocation;
use Innmind\Url\Url;

/**
 * @psalm-immutable
 */
final class NoOp implements RewriteLocation
{
    #[\Override]
    public function __invoke(Url $location): Url
    {
        return $location;
    }
}
