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

    public function root(): Node
    {
        return $this->root;
    }

    /**
     * @return Set<Node>
     */
    public function nodes(): Set
    {
        return ($this->nodes)($this->root);
    }

    public function removeDependenciesSubGraph(): self
    {
        if ($this->root->dependency()) {
            return new self(
                $this->root->removeRelations(),
                $this->nodes->clear(),
            );
        }

        $toRemove = $this
            ->nodes
            ->filter(static fn($node) => $node->dependency())
            ->flatMap(static fn($node) => $node->relations())
            ->map(static fn($relation) => $relation->reference())
            ->flatMap(
                fn($reference) => $this
                    ->nodes
                    ->find(static fn($node) => $reference->equals($node->reference()))
                    ->map(static fn($node) => $node->relations()->map(
                        static fn($relation) => $relation->reference(),
                    ))
                    ->match(
                        static fn($references) => ($references)($reference),
                        static fn() => Set::of($reference),
                    ),
            );
        $nodes = $this
            ->nodes
            ->filter(static fn($node) => !$toRemove->any(
                static fn($reference) => $reference->equals($node->reference()),
            ))
            ->map(static fn($node) => match ($node->dependency()) {
                true => $node->removeRelations(),
                false => $node,
            });

        return new self($this->root, $nodes);
    }
}
