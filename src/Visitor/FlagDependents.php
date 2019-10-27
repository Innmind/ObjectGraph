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

final class FlagDependents
{
    private $dependencies;

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

        if ($this->isDependent($node)) {
            $node->flagAsDependent();
        }

        return $node->relations()->reduce(
            $visited->add($node),
            function(SetInterface $visited, Relation $relation): SetInterface
            {
                return $this->visit($relation->node(), $visited);
            }
        );
    }

    private function isDependent(Node $node): bool
    {
        return $this->dependencies->reduce(
            false,
            static function(bool $isDependent, object $dependency) use ($node): bool {
                return $isDependent || $node->dependsOn($dependency);
            }
        );
    }
}
