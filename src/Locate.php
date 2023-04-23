<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Url\Url;

final class Locate
{
    private function __construct()
    {
    }

    public function __invoke(Graph $graph): Graph
    {
        return $graph->mapNode(static fn($node) => $node->locatedAt(Url::of(
            'file://'.(new \ReflectionClass($node->class()->toString()))->getFileName(),
        )));
    }

    public static function of(): self
    {
        return new self;
    }
}
