<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Filesystem\File\Content;
use Innmind\Graphviz;
use Innmind\Colour\RGBA;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Render
{
    private RewriteLocation $rewriteLocation;
    private Graphviz\Graph\Rankdir $direction;

    private function __construct(
        RewriteLocation $rewriteLocation,
        Graphviz\Graph\Rankdir $direction,
    ) {
        $this->rewriteLocation = $rewriteLocation;
        $this->direction = $direction;
    }

    public function __invoke(Graph $graph): Content
    {
        $graphviz = Graphviz\Graph::directed('G', $this->direction)->add(
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

    /**
     * @psalm-pure
     */
    public static function of(?RewriteLocation $rewriteLocation = null): self
    {
        return new self(
            $rewriteLocation ?? new RewriteLocation\NoOp,
            Graphviz\Graph\Rankdir::leftToRight,
        );
    }

    public function fromTopToBottom(): self
    {
        return new self($this->rewriteLocation, Graphviz\Graph\Rankdir::topToBottom);
    }

    private function render(Node $node): Graphviz\Node
    {
        /** @psalm-suppress ArgumentTypeCoercion As a class name can't be empty */
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

    /**
     * @psalm-pure
     */
    private static function name(Node\Reference $reference): Graphviz\Node\Name
    {
        return Graphviz\Node\Name::of('object_'.$reference->toString());
    }
}
