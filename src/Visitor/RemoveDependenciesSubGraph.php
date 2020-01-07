<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\Set;

final class RemoveDependenciesSubGraph
{
    public function __invoke(Node $node): void
    {
        $this->visit($node, Set::of(Node::class));
    }

    private function visit(Node $node, Set $visited): Set
    {
        if ($visited->contains($node)) {
            return $visited;
        }

        if ($node->isDependency()) {
            $node->removeRelations();
        }

        return $node->relations()->reduce(
            ($visited)($node),
            function(Set $visited, Relation $relation): Set {
                return $this->visit($relation->node(), $visited);
            },
        );
    }
}
