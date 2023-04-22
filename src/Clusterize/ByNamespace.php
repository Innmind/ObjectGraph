<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Clusterize;

use Innmind\ObjectGraph\{
    Clusterize,
    Node,
    NamespacePattern,
};
use Innmind\Graphviz;
use Innmind\Immutable\{
    Map,
    Set,
};

final class ByNamespace implements Clusterize
{
    /** @var Map<NamespacePattern, string> */
    private Map $clusters;

    /**
     * @param Map<NamespacePattern, string> $clusters
     */
    public function __construct(Map $clusters)
    {
        $this->clusters = $clusters;
    }

    public function __invoke(Map $nodes): Set
    {
        $graphs = $this->clusters->flatMap(static fn($_, $name) => Map::of([
            $name,
            Graphviz\Graph::directed($name, Graphviz\Graph\Rankdir::leftToRight)->displayAs($name),
        ]));
        $clusters = $this->clusters->flatMap(static fn($pattern, $name) => Map::of([
            $graphs->get($name)->match(
                static fn($graph) => $graph,
                static fn() => throw new \LogicException,
            ),
            $pattern,
        ]));
        $clusters = $clusters->flatMap(
            fn($cluster, $pattern) => Map::of([
                $this->cluster($nodes, $cluster, $pattern),
                $pattern,
            ]),
        );

        /** @var Set<Graphviz\Graph> */
        return $clusters
            ->keys()
            ->filter(static function(Graphviz\Graph $graph): bool {
                return $graph->roots()->size() > 0;
            });
    }

    /**
     * @param Map<Node, Graphviz\Node> $nodes
     */
    private function cluster(
        Map $nodes,
        Graphviz\Graph $cluster,
        NamespacePattern $pattern,
    ): Graphviz\Graph {
        return $nodes
            ->filter(static function(Node $node) use ($pattern): bool {
                return $node->class()->in($pattern);
            })
            ->values()
            ->reduce(
                $cluster,
                static fn(Graphviz\Graph $cluster, Graphviz\Node $node) => $cluster->add(
                    Graphviz\Node::of($node->name()),
                ),
            );
    }
}
