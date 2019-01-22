<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Graphviz;
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
};

interface Clusterize
{
    /**
     * @param MapInterface<Node, Graphviz\Node> $nodes
     *
     * @return SetInterface<Graphviz\Graph>
     */
    public function __invoke(MapInterface $nodes): SetInterface;
}
