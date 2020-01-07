<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Graphviz;
use Innmind\Immutable\{
    Map,
    Set,
};

interface Clusterize
{
    /**
     * @param Map<Node, Graphviz\Node> $nodes
     *
     * @return Set<Graphviz\Graph>
     */
    public function __invoke(Map $nodes): Set;
}
