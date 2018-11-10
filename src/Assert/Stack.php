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
};

final class Stack
{
    private $stack;

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
        return $this->visit($node, $this->stack)->size() === 0;
    }

    private function visit(Node $node, StreamInterface $stack): StreamInterface
    {
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
