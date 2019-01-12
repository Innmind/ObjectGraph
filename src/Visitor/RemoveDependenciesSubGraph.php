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

final class RemoveDependenciesSubGraph
{
    public function __invoke(Node $node): void
    {
        $this->visit($node, Set::of(Node::class));
    }

    private function visit(Node $node, SetInterface $visited): SetInterface
    {
        if ($visited->contains($node)) {
            return $visited;
        }

        if ($node->isDependency()) {
            $node->removeRelations();
        }

        return $node->relations()->reduce(
            $visited->add($node),
            function(SetInterface $visited, Relation $relation): SetInterface {
                return $this->visit($relation->node(), $visited);
            }
        );
    }
}
