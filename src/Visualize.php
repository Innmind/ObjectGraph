<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Graphviz;
use Innmind\Colour\RGBA;
use Innmind\Stream\Readable;
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
        Clusterize $clusterize = null
    ) {
        /** @var Map<Node, Graphviz\Node> */
        $this->nodes = Map::of(Node::class, Graphviz\Node::class);
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
                return Set::of(Graphviz\Graph::class);
            }
        };
    }

    public function __invoke(Node $node): Readable
    {
        try {
            $this->nodes = $this->nodes->clear();

            $graph = Graphviz\Graph\Graph::directed(
                'G',
                Graphviz\Graph\Rankdir::leftToRight(),
            );

            $root = $this->visit($node);
            $root->shaped(
                Graphviz\Node\Shape::hexagon()
                    ->fillWithColor(RGBA::of('#0f0')
                ),
            );
            $graph->add($root);
            ($this->clusterize)($this->nodes)->foreach(
                static function(Graphviz\Graph $cluster) use ($graph): void {
                    $graph->cluster($cluster);
                },
            );

            return (new Graphviz\Layout\Dot)($graph);
        } finally {
            $this->nodes = $this->nodes->clear();
        }
    }

    private function visit(Node $node): Graphviz\Node
    {
        if ($this->nodes->contains($node)) {
            return $this->nodes->get($node);
        }

        $dotNode = Graphviz\Node\Node::named('object_'.$node->reference()->toString());
        $dotNode->displayAs(
            Str::of($node->class()->toString())
                ->replace("\x00", '') // remove the invisible character used in the name of anonymous classes
                ->replace('\\', '\\\\')
                ->toString(),
        );
        $dotNode->target(
            ($this->rewriteLocation)($node->location()),
        );

        if ($node->isDependent()) {
            $dotNode->shaped(
                Graphviz\Node\Shape::Mrecord()
                    ->fillWithColor(RGBA::of('#00b6ff')),
            );
        }

        if ($node->isDependency()) {
            $dotNode->shaped(
                Graphviz\Node\Shape::box()
                    ->fillWithColor(RGBA::of('#ffb600')),
            );
        }

        if ($node->highlighted()) {
            $dotNode->shaped(
                Graphviz\Node\Shape::ellipse()
                    ->withColor(RGBA::of('#0f0'))
                    ->fillWithColor(RGBA::of('#0f0')),
            );
        }

        $this->nodes = ($this->nodes)($node, $dotNode);

        $node->relations()->foreach(function(Relation $relation) use ($dotNode): void {
            $child = $this->visit($relation->node());

            $edge = $dotNode->linkedTo($child);
            $edge->displayAs($relation->property()->toString());

            if ($relation->highlighted()) {
                $edge->bold();
                $edge->useColor(RGBA::of('#0f0'));
            }
        });

        return $dotNode;
    }
}
