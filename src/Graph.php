<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Immutable\Map;

final class Graph
{
    private Graph\Visit $visit;

    public function __construct()
    {
        $this->visit = new Graph\Delegate(
            new Graph\ParseSplObjectStorage,
            new Graph\ParseIterable,
            new Graph\ParseSetAndSequence,
            new Graph\ParseMap,
            new Graph\ExtractProperties,
        );
    }

    public function __invoke(object $root): Node
    {
        $nodes = ($this->visit)(
            Map::of(),
            $root,
            $this->visit,
        );

        return $nodes->get($root)->match(
            static fn($node) => $node,
            static fn() => throw new \LogicException,
        );
    }
}
