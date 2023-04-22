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
use function Innmind\Immutable\{
    assertMap,
    unwrap,
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
        assertMap(NamespacePattern::class, 'string', $clusters, 1);

        $this->clusters = $clusters;
    }

    public function __invoke(Map $nodes): Set
    {
        $graphs = $this->clusters->values()->toMapOf(
            'string',
            Graphviz\Graph::class,
            static function(string $name): \Generator {
                $graph = Graphviz\Graph\Graph::directed(
                    $name,
                    Graphviz\Graph\Rankdir::leftToRight(),
                );
                $graph->displayAs($name);

                yield $name => $graph;
            },
        );
        $clusters = $this->clusters->toMapOf(
            Graphviz\Graph::class,
            NamespacePattern::class,
            static function(NamespacePattern $pattern, string $name) use ($graphs): \Generator {
                yield $graphs->get($name) => $pattern;
            },
        );
        $clusters->foreach(function(Graphviz\Graph $cluster, NamespacePattern $pattern) use ($nodes): void {
            $this->cluster($nodes, $cluster, $pattern);
        });

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
    ): void {
        $nodes
            ->filter(static function(Node $node) use ($pattern): bool {
                return $node->class()->in($pattern);
            })
            ->values()
            ->foreach(static function(Graphviz\Node $node) use ($cluster): void {
                $cluster->add(
                    new Graphviz\Node\Node($node->name()),
                );
            });
    }
}
