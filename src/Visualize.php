<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Exception\RecursiveGraph;
use Innmind\Graphviz;
use Innmind\Colour\RGBA;
use Innmind\Stream\Readable;
use Innmind\Url\{
    SchemeInterface,
    Scheme,
    UrlInterface,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface,
    Set,
    Str,
};

final class Visualize
{
    private ?MapInterface $nodes = null;
    private LocationRewriter $rewriteLocation;
    private Clusterize $clusterize;

    public function __construct(
        LocationRewriter $rewriteLocation = null,
        Clusterize $clusterize = null
    ) {
        $this->rewriteLocation = $rewriteLocation ?? new class implements LocationRewriter {
            public function __invoke(UrlInterface $location): UrlInterface
            {
                return $location;
            }
        };
        $this->clusterize = $clusterize ?? new class implements Clusterize {
            public function __invoke(MapInterface $nodes): SetInterface
            {
                return Set::of(Graphviz\Graph::class);
            }
        };
    }

    public function __invoke(Node $node): Readable
    {
        try {
            $this->nodes = Map::of(Node::class, Graphviz\Node::class);
            $graph = Graphviz\Graph\Graph::directed(
                'G',
                Graphviz\Graph\Rankdir::leftToRight()
            );

            $graph->add(
                $this
                    ->visit($node)
                    ->shaped(
                        Graphviz\Node\Shape::hexagon()
                            ->fillWithColor(RGBA::fromString('#0f0')
                        )
                    )
            );
            $graph = ($this->clusterize)($this->nodes)->reduce(
                $graph,
                static function(Graphviz\Graph $graph, Graphviz\Graph $cluster): Graphviz\Graph {
                    return $graph->cluster($cluster);
                }
            );

            return (new Graphviz\Layout\Dot)($graph);
        } finally {
            $this->nodes = null;
        }
    }

    private function visit(Node $node): Graphviz\Node
    {
        if ($this->nodes->contains($node)) {
            return $this->nodes->get($node);
        }

        $dotNode = Graphviz\Node\Node::named('object_'.$node->reference()->toString())
            ->displayAs(
                (string) Str::of($node->class()->toString())
                    ->replace("\x00", '') // remove the invisible character used in the name of anonymous classes
                    ->replace('\\', '\\\\')
            )
            ->target(
                ($this->rewriteLocation)($node->location())
            );

        if ($node->isDependent()) {
            $dotNode->shaped(
                Graphviz\Node\Shape::Mrecord()
                    ->fillWithColor(RGBA::fromString('#00b6ff'))
            );
        }

        if ($node->isDependency()) {
            $dotNode->shaped(
                Graphviz\Node\Shape::box()
                    ->fillWithColor(RGBA::fromString('#ffb600'))
            );
        }

        if ($node->highlighted()) {
            $dotNode->shaped(
                Graphviz\Node\Shape::ellipse()
                    ->withColor(RGBA::fromString('#0f0'))
                    ->fillWithColor(RGBA::fromString('#0f0'))
            );
        }

        $this->nodes = $this->nodes->put($node, $dotNode);

        $node->relations()->foreach(function(Relation $relation) use ($dotNode): void {
            $child = $this->visit($relation->node());

            $edge = $dotNode
                ->linkedTo($child)
                ->displayAs($relation->property()->toString());

            if ($relation->highlighted()) {
                $edge
                    ->bold()
                    ->useColor(RGBA::fromString('#0f0'));
            }
        });

        return $dotNode;
    }
}
