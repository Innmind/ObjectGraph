<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\LocationRewriter;

use Innmind\ObjectGraph\LocationRewriter;
use Innmind\Url\Url;

final class NoOp implements LocationRewriter
{
    public function __invoke(Url $location): Url
    {
        return $location;
    }
}
