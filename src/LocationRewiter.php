<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Url\UrlInterface;

interface LocationRewiter
{
    public function __invoke(UrlInterface $location): UrlInterface;
}
