<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Node,
    Relation,
    Exception\ObjectNotFound,
};

final class AccessObjectNode
{
    private $object;

    public function __construct(object $object)
    {
        $this->object = $object;
    }

    /**
     * @throws ObjectNotFound
     */
    public function __invoke(Node $node): Node
    {
        $target = $this->visit($node);

        if (\is_null($target)) {
            throw new ObjectNotFound;
        }

        return $target;
    }

    private function visit(Node $node): ?Node
    {
        if ($node->comesFrom($this->object)) {
            return $node;
        }

        return $node->relations()->reduce(
            null,
            function(?Node $target, Relation $relation): ?Node {
                return $this->visit($relation->node());
            }
        );
    }
}
