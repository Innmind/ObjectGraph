<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Filesystem\File\Content;
use Innmind\Graphviz;
use Innmind\Immutable\{
    Set,
    Str,
};

final class Render
{
    private function __construct()
    {
    }

    /**
     * @param Set<Node> $nodes
     */
    public function __invoke(Set $nodes): Content
    {
        $graph = Graphviz\Graph::directed('G', Graphviz\Graph\Rankdir::leftToRight);
        $graph = $nodes
            ->map(self::render(...))
            ->reduce(
                $graph,
                static fn(Graphviz\Graph $graph, $node) => $graph->add($node),
            );

        return Graphviz\Layout\Dot::of()($graph);
    }

    public static function of(): self
    {
        return new self;
    }

    private static function render(Node $node): Graphviz\Node
    {
        $render = Graphviz\Node::of(self::name($node))
            ->displayAs(
                Str::of($node->class()->toString())
                    ->replace("\x00", '') // remove the invisible character used in the name of anonymous classes
                    ->replace('\\', '\\\\')
                    ->toString(),
            );

        return $node
            ->relations()
            ->reduce(
                $render,
                static fn(Graphviz\Node $render, $relation) => $render->linkedTo(
                    self::name($relation->node()),
                    static fn($edge) => $edge->displayAs($relation->property()->toString()),
                ),
            );
    }

    private static function name(Node $node): Graphviz\Node\Name
    {
        return Graphviz\Node\Name::of('object_'.$node->reference()->toString());
    }
}
