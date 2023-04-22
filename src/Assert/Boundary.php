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
    /** @var Set<NamespacePattern> */
    private Set $exclusions;

    /**
     * @no-named-arguments
     */
    public function __construct(
        NamespacePattern $namespace,
        NamespacePattern $exclusion,
        NamespacePattern ...$exclusions,
    ) {
        $this->namespace = $namespace;
        $this->exclusions = Set::of($exclusion, ...$exclusions);
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

    /**
     * @no-named-arguments
     *
     * @param literal-string $namespace
     * @param literal-string $exclusion
     * @param literal-string $exclusions
     */
    public static function of(
        string $namespace,
        string $exclusion,
        string ...$exclusions,
    ): self {
        return new self(
            NamespacePattern::of($namespace),
            NamespacePattern::of($exclusion),
            ...\array_map(NamespacePattern::of(...), $exclusions),
        );
    }

    private function visit(Node $node): void
    {
        if ($node->class()->in($this->namespace)) {
            $this->assert($node);

            return;
        }

        $_ = $node->relations()->foreach(function(Relation $relation): void {
            $this->visit($relation->node());
        });
    }

    private function assert(Node $node): void
    {
        $_ = $this->exclusions->foreach(static function(NamespacePattern $namespace) use ($node): void {
            if ($node->class()->in($namespace)) {
                throw new \Exception;
            }
        });

        $_ = $node->relations()->foreach(function(Relation $relation): void {
            $this->assert($relation->node());
        });
    }
}
