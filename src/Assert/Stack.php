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
    /** @var Set<Node>|null */
    private ?Set $nodes = null;

    private function __construct(string ...$classes)
    {
        $this->stack = Sequence::strings(...$classes);
    }

    public static function of(string ...$classes): self
    {
        return new self(...$classes);
    }

    public function __invoke(Node $node): bool
    {
        try {
            $this->nodes = Set::of(Node::class);

            return $this->visit($node, $this->stack)->size() === 0;
        } finally {
            $this->nodes = null;
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
