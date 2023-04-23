<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Immutable\Set;

/**
 * @psalm-immutable
 */
final class Graph
{
    private Node $root;
    /** @var Set<Node> */
    private Set $nodes;

    /**
     * @param Set<Node> $nodes
     */
    private function __construct(Node $root, Set $nodes)
    {
        $this->root = $root;
        $this->nodes = $nodes;
    }

    /**
     * @psalm-pure
     *
     * @param Set<Node> $nodes
     */
    public static function of(Node $root, Set $nodes): self
    {
        return new self($root, $nodes->remove($root));
    }

    /**
     * @param callable(Node): Node $map
     */
    public function mapNode(callable $map): self
    {
        /** @psalm-suppress ImpureFunctionCall */
        return new self(
            $map($this->root),
            $this->nodes->map($map),
        );
    }

    /**
     * @return Set<Node>
     */
    public function nodes(): Set
    {
        return ($this->nodes)($this->root);
    }
}
