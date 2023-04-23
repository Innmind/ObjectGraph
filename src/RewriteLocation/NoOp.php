<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\RewriteLocation;

use Innmind\ObjectGraph\RewriteLocation;
use Innmind\Url\Url;

final class NoOp implements RewriteLocation
{
    public function __invoke(Url $location): Url
    {
        return $location;
    }
}
