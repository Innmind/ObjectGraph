<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Acyclic
{
    public function __invoke(Node $node): bool
    {
        try {
            $this->visit($node, Set::of(Node::class));

            return false;
        } catch (\Exception $e) {
            return true;
        }
    }

    private function visit(Node $node, SetInterface $visiting): void
    {
        $visiting = $visiting->add($node);
        $node->relations()->foreach(function(Relation $relation) use ($visiting): void {
            if ($visiting->contains($relation->node())) {
                throw new \Exception;
            }

            $this->visit($relation->node(), $visiting);
        });
    }
}
