<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
    Set,
};

final class Stack
{
    private $stack;
    private $nodes;

    private function __construct(string ...$classes)
    {
        $this->stack = Stream::of('string', ...$classes);
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

    private function visit(Node $node, StreamInterface $stack): StreamInterface
    {
        if ($this->nodes->contains($node)) {
            return $stack;
        }

        $this->nodes = $this->nodes->add($node);

        if ($stack->size() === 0) {
            return $stack;
        }

        if ($stack->first() === (string) $node->class()) {
            $stack = $stack->drop(1);
        }

        return $node->relations()->reduce(
            $stack,
            function(StreamInterface $stack, Relation $relation): StreamInterface {
                return $this->visit($relation->node(), $stack);
            }
        );
    }
}
