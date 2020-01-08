<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\{
    Sequence,
    Set,
};

final class Stack
{
    /** @var Sequence<string> */
    private Sequence $stack;
    /** @var Set<Node> */
    private Set $nodes;

    private function __construct(string ...$classes)
    {
        /** @var Sequence<string> */
        $this->stack = Sequence::strings(...$classes);
        /** @var Set<Node> */
        $this->nodes = Set::of(Node::class);
    }

    public static function of(string ...$classes): self
    {
        return new self(...$classes);
    }

    public function __invoke(Node $node): bool
    {
        try {
            $this->nodes = $this->nodes->clear();

            return $this->visit($node, $this->stack)->size() === 0;
        } finally {
            $this->nodes = $this->nodes->clear();
        }
    }

    private function visit(Node $node, Sequence $stack): Sequence
    {
        if ($this->nodes->contains($node)) {
            return $stack;
        }

        $this->nodes = ($this->nodes)($node);

        if ($stack->size() === 0) {
            return $stack;
        }

        if ($stack->first() === $node->class()->toString()) {
            $stack = $stack->drop(1);
        }

        return $node->relations()->reduce(
            $stack,
            function(Sequence $stack, Relation $relation): Sequence {
                return $this->visit($relation->node(), $stack);
            },
        );
    }
}
