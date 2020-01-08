<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Node,
    Relation,
    NamespacePattern,
};
use Innmind\Immutable\Set;

final class Boundary
{
    private NamespacePattern $namespace;
    private Set $exclusions;

    public function __construct(
        NamespacePattern $namespace,
        NamespacePattern $exclusion,
        NamespacePattern ...$exclusions
    ) {
        $this->namespace = $namespace;
        $this->exclusions = Set::of(NamespacePattern::class, $exclusion, ...$exclusions);
    }

    public static function of(
        string $namespace,
        string $exclusion,
        string ...$exclusions
    ): self {
        return new self(
            new NamespacePattern($namespace),
            new NamespacePattern($exclusion),
            ...array_map(static function(string $exclusion): NamespacePattern {
                return new NamespacePattern($exclusion);
            }, $exclusions),
        );
    }

    public function __invoke(Node $node): bool
    {
        try {
            $this->visit($node);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function visit(Node $node): void
    {
        if ($node->class()->in($this->namespace)) {
            $this->assert($node);

            return;
        }

        $node->relations()->foreach(function(Relation $relation): void {
            $this->visit($relation->node());
        });
    }

    private function assert(Node $node): void
    {
        $this->exclusions->foreach(function(NamespacePattern $namespace) use ($node): void {
            if ($node->class()->in($namespace)) {
                throw new \Exception;
            }
        });

        $node->relations()->foreach(function(Relation $relation): void {
            $this->assert($relation->node());
        });
    }
}
