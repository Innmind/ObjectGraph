<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Exception\RecursiveGraph;
use Innmind\Graphviz;
use Innmind\Colour\RGBA;
use Innmind\Stream\Readable;
use Innmind\Immutable\Map;

final class Visualize
{
    private $nodes;

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

        $dotNode = Graphviz\Node\Node::named('object_'.$node->reference())
            ->displayAs(\str_replace('\\', '\\\\', (string) $node->class()));
        $this->nodes = $this->nodes->put($node, $dotNode);

        $node->relations()->foreach(function(Relation $relation) use ($dotNode): void {
            $child = $this->visit($relation->node());

            $dotNode
                ->linkedTo($child)
                ->displayAs((string) $relation->property());
        });

        return $dotNode;
    }
}
