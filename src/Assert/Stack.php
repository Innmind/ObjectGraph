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

    /**
     * @no-named-arguments
     */
    private function __construct(string ...$classes)
    {
        $this->stack = Sequence::strings(...$classes);
        /** @var Set<Node> */
        $this->nodes = Set::of();
    }

    public function __invoke(Node $node): bool
    {
        try {
            $this->nodes = $this->nodes->clear();

            return $this->visit($this->stack, $node)->size() === 0;
        } finally {
            $this->nodes = $this->nodes->clear();
        }
    }

    /**
     * @no-named-arguments
     */
    public static function of(string ...$classes): self
    {
        return new self(...$classes);
    }

    /**
     * @param Sequence<string> $stack
     *
     * @return Sequence<string>
     */
    private function visit(Sequence $stack, Node $node): Sequence
    {
        if ($this->nodes->contains($node)) {
            return $stack;
        }

        $this->nodes = ($this->nodes)($node);

        if ($stack->size() === 0) {
            return $stack;
        }

        $first = $stack->first()->match(
            static fn($first) => $first,
            static fn() => throw new \LogicException,
        );

        if ($first === $node->class()->toString()) {
            $stack = $stack->drop(1);
        }

        return $node
            ->relations()
            ->map(static fn($relation) => $relation->node())
            ->reduce(
                $stack,
                $this->visit(...),
            );
    }
}
