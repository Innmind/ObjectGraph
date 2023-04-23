<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Filesystem\File\Content;
use Innmind\Graphviz;
use Innmind\Colour\RGBA;
use Innmind\Immutable\{
    Set,
    Str,
};

final class Render
{
    private LocationRewriter $rewriteLocation;

    private function __construct(LocationRewriter $rewriteLocation)
    {
        $this->rewriteLocation = $rewriteLocation;
    }

    public function __invoke(Graph $graph): Content
    {
        $graphviz = Graphviz\Graph::directed('G', Graphviz\Graph\Rankdir::leftToRight)->add(
            $this
                ->render($graph->root())
                ->shaped(
                    Graphviz\Node\Shape::hexagon()->fillWithColor(RGBA::of('#0f0')),
                ),
        );
        $graphviz = $graph
            ->nodes()
            ->remove($graph->root())
            ->map($this->render(...))
            ->reduce(
                $graphviz,
                static fn(Graphviz\Graph $graph, $node) => $graph->add($node),
            );

        return Graphviz\Layout\Dot::of()($graphviz);
    }

    public static function of(LocationRewriter $rewriteLocation = null): self
    {
        return new self($rewriteLocation ?? new LocationRewriter\NoOp);
    }

    private function render(Node $node): Graphviz\Node
    {
        $render = Graphviz\Node::of(self::name($node->reference()))
            ->displayAs(
                Str::of($node->class()->toString())
                    ->replace("\x00", '') // remove the invisible character used in the name of anonymous classes
                    ->replace('\\', '\\\\')
                    ->toString(),
            );
        $render = $node
            ->location()
            ->map(fn($node) => ($this->rewriteLocation)($node))
            ->match(
                static fn($location) => $render->target($location),
                static fn() => $render,
            );

        if ($node->dependency()) {
            $render = $render->shaped(
                Graphviz\Node\Shape::box()->fillWithColor(RGBA::of('#ffb600')),
            );
        }

        return $node
            ->relations()
            ->reduce(
                $render,
                static fn(Graphviz\Node $render, $relation) => $render->linkedTo(
                    self::name($relation->reference()),
                    static fn($edge) => $edge->displayAs($relation->property()->toString()),
                ),
            );
    }

    private static function name(Node\Reference $reference): Graphviz\Node\Name
    {
        return Graphviz\Node\Name::of('object_'.$reference->toString());
    }
}
