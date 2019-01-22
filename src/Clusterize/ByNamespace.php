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
    MapInterface,
    Map,
    SetInterface,
    Set,
    Pair,
};
use function Innmind\Immutable\assertMap;

final class ByNamespace implements Clusterize
{
    private $clusters;

    public function __construct(MapInterface $clusters)
    {
        assertMap(NamespacePattern::class, 'string', $clusters, 1);

        $this->clusters = $clusters;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(MapInterface $nodes): SetInterface
    {
        $graphs = $this->clusters->values()->reduce(
            Map::of('string', Graphviz\Graph::class),
            static function(MapInterface $graphs, string $name): MapInterface {
                return $graphs->put(
                    $name,
                    Graphviz\Graph\Graph::directed(
                        $name,
                        Graphviz\Graph\Rankdir::leftToRight()
                    )->displayAs($name)
                );
            }
        );
        $clusters = $this
            ->clusters
            ->reduce(
                Map::of(Graphviz\Graph::class, NamespacePattern::class),
                static function(MapInterface $clusters, NamespacePattern $pattern, string $name) use ($graphs): MapInterface {
                    return $clusters->put(
                        $graphs->get($name),
                        $pattern
                    );
                }
            )
            ->map(function(Graphviz\Graph $cluster, NamespacePattern $pattern) use ($nodes): Pair {
                return new Pair(
                    $this->cluster($nodes, $cluster, $pattern),
                    $pattern
                );
            })
            ->keys()
            ->filter(static function(Graphviz\Graph $graph): bool {
                return $graph->roots()->size() > 0;
            });

        return Set::of(Graphviz\Graph::class, ...$clusters);
    }

    /**
     * @param MapInterface<Node, Graphviz\Node> $nodes
     */
    private function cluster(
        MapInterface $nodes,
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
                    return $cluster->add(
                        new Graphviz\Node\Node($node->name())
                    );
                }
            );
    }
}
