<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Graphviz;
use Innmind\Filesystem\File\Content;
use Innmind\Colour\RGBA;
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    Set,
    Str,
};

final class Visualize
{
    /** @var Map<Node, Graphviz\Node> */
    private Map $nodes;
    private LocationRewriter $rewriteLocation;
    private Clusterize $clusterize;

    public function __construct(
        LocationRewriter $rewriteLocation = null,
        Clusterize $clusterize = null,
    ) {
        /** @var Map<Node, Graphviz\Node> */
        $this->nodes = Map::of();
        $this->rewriteLocation = $rewriteLocation ?? new class implements LocationRewriter {
            public function __invoke(Url $location): Url
            {
                return $location;
            }
        };
        $this->clusterize = $clusterize ?? new class implements Clusterize {
            public function __invoke(Map $nodes): Set
            {
                /** @var Set<Graphviz\Graph> */
                return Set::of();
            }
        };
    }

    public function __invoke(Node $node): Content
    {
        try {
            $this->nodes = $this->nodes->clear();

            $graph = Graphviz\Graph::directed(
                'G',
                Graphviz\Graph\Rankdir::leftToRight,
            );

            $root = $this->visit($node);
            $root = $root->shaped(
                Graphviz\Node\Shape::hexagon()
                    ->fillWithColor(RGBA::of('#0f0')),
            );
            $graph = ($this->nodes)($node, $root)
                ->values()
                ->reduce(
                    $graph,
                    static fn(Graphviz\Graph $graph, $node) => $graph->add($node),
                );
            $graph = ($this->clusterize)($this->nodes)->reduce(
                $graph,
                static fn(Graphviz\Graph $graph, Graphviz\Graph $cluster) => $graph->cluster($cluster),
            );

            return Graphviz\Layout\Dot::of()($graph);
        } finally {
            $this->nodes = $this->nodes->clear();
        }
    }

    private function visit(Node $node): Graphviz\Node
    {
        if ($this->nodes->contains($node)) {
            return $this->nodes->get($node)->match(
                static fn($node) => $node,
                static fn() => throw new \LogicException,
            );
        }

        $dotNode = Graphviz\Node::named('object_'.$node->reference()->toString())
            ->displayAs(
                Str::of($node->class()->toString())
                    ->replace("\x00", '') // remove the invisible character used in the name of anonymous classes
                    ->replace('\\', '\\\\')
                    ->toString(),
            )
            ->target(
                ($this->rewriteLocation)($node->location()),
            );

        if ($node->isDependent()) {
            $dotNode = $dotNode->shaped(
                Graphviz\Node\Shape::Mrecord()
                    ->fillWithColor(RGBA::of('#00b6ff')),
            );
        }

        if ($node->isDependency()) {
            $dotNode = $dotNode->shaped(
                Graphviz\Node\Shape::box()
                    ->fillWithColor(RGBA::of('#ffb600')),
            );
        }

        if ($node->highlighted()) {
            $dotNode = $dotNode->shaped(
                Graphviz\Node\Shape::ellipse()
                    ->withColor(RGBA::of('#0f0'))
                    ->fillWithColor(RGBA::of('#0f0')),
            );
        }

        $this->nodes = ($this->nodes)($node, $dotNode);

        $dotNode = $node
            ->relations()
            ->reduce(
                $dotNode,
                function(Graphviz\Node $dotNode, Relation $relation) {
                    $child = $this->visit($relation->node());

                    return $dotNode->linkedTo(
                        $child->name(),
                        static function($edge) use ($relation) {
                            $edge = $edge->displayAs($relation->property()->toString());

                            if ($relation->highlighted()) {
                                $edge = $edge
                                    ->bold()
                                    ->useColor(RGBA::of('#0f0'));
                            }

                            return $edge;
                        },
                    );
                },
            );
        $this->nodes = ($this->nodes)($node, $dotNode);

        return $dotNode;
    }
}
