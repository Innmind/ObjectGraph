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
    Pair,
};
use function Innmind\Immutable\{
    assertMap,
    unwrap,
};

final class ByNamespace implements Clusterize
{
    private Map $clusters;

    public function __construct(Map $clusters)
    {
        assertMap(NamespacePattern::class, 'string', $clusters, 1);

        $this->clusters = $clusters;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Map $nodes): Set
    {
        $graphs = $this->clusters->values()->reduce(
            Map::of('string', Graphviz\Graph::class),
            static function(Map $graphs, string $name): Map {
                $graph = Graphviz\Graph\Graph::directed(
                    $name,
                    Graphviz\Graph\Rankdir::leftToRight(),
                );
                $graph->displayAs($name);

                return ($graphs)($name, $graph);
            },
        );
        $clusters = $this
            ->clusters
            ->reduce(
                Map::of(Graphviz\Graph::class, NamespacePattern::class),
                static function(Map $clusters, NamespacePattern $pattern, string $name) use ($graphs): Map {
                    return ($clusters)(
                        $graphs->get($name),
                        $pattern,
                    );
                },
            )
            ->map(function(Graphviz\Graph $cluster, NamespacePattern $pattern) use ($nodes): Pair {
                return new Pair(
                    $this->cluster($nodes, $cluster, $pattern),
                    $pattern,
                );
            })
            ->keys()
            ->filter(static function(Graphviz\Graph $graph): bool {
                return $graph->roots()->size() > 0;
            });

        return Set::of(Graphviz\Graph::class, ...unwrap($clusters));
    }

    /**
     * @param Map<Node, Graphviz\Node> $nodes
     */
    private function cluster(
        Map $nodes,
        Graphviz\Graph $cluster,
        NamespacePattern $pattern
    ): Graphviz\Graph {
        return $nodes
            ->filter(static function(Node $node) use ($pattern): bool {
                return $node->class()->in($pattern);
            })
            ->values()
            ->reduce(
                $cluster,
                static function(Graphviz\Graph $cluster, Graphviz\Node $node): Graphviz\Graph {
                    $cluster->add(
                        new Graphviz\Node\Node($node->name()),
                    );

                    return $cluster;
                },
            );
    }
}
