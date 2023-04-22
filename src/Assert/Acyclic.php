<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\Set;

final class Acyclic
{
    public function __invoke(Node $node): bool
    {
        try {
            $this->visit($node, Set::of());

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function visit(Node $node, Set $visiting): void
    {
        $visiting = ($visiting)($node);
        $_ = $node->relations()->foreach(function(Relation $relation) use ($visiting): void {
            if ($visiting->contains($relation->node())) {
                throw new \Exception;
            }

            $this->visit($relation->node(), $visiting);
        });
    }
}
