<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Url\Url;
use Innmind\Immutable\Set;

final class Locate
{
    private function __construct()
    {
    }

    /**
     * @param Set<Node> $nodes
     *
     * @return Set<Node>
     */
    public function __invoke(Set $nodes): Set
    {
        return $nodes->map(static fn($node) => $node->locatedAt(Url::of(
            'file://'.(new \ReflectionClass($node->class()->toString()))->getFileName(),
        )));
    }

    public static function of(): self
    {
        return new self;
    }
}
