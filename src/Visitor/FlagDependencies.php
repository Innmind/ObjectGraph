<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class FlagDependencies
{
    private Set $dependencies;

    public function __construct(object ...$dependencies)
    {
        $this->dependencies = Set::of('object', ...$dependencies);
    }

    public function __invoke(Node $node): void
    {
        $this->visit($node, Set::of(Node::class));
    }

    private function visit(Node $node, SetInterface $visited): SetInterface
    {
        if ($visited->contains($node)) {
            return $visited;
        }

        if ($this->isDependency($node)) {
            $node->flagAsDependency();
        }

        return $node->relations()->reduce(
            $visited->add($node),
            function(SetInterface $visited, Relation $relation): SetInterface
            {
                return $this->visit($relation->node(), $visited);
            }
        );
    }

    private function isDependency(Node $node): bool
    {
        return $this->dependencies->reduce(
            false,
            static function(bool $isDependency, object $dependency) use ($node): bool {
                return $isDependency || $node->comesFrom($dependency);
            }
        );
    }
}
