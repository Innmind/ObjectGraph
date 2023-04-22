<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\{
    Map,
    Set,
    Sequence,
};

final class ParseSetAndSequence implements Visit
{
    /**
     * @param Map<object, Node> $nodes
     *
     * @return Map<object, Node>
     */
    public function __invoke(
        Map $nodes,
        object $object,
        Visit $visit,
    ): Map {
        if ($nodes->contains($object)) {
            /** @var Map<object, Node> */
            return $nodes;
        }

        if (!$object instanceof Set && !$object instanceof Sequence) {
            /** @var Map<object, Node> */
            return $nodes;
        }

        $node = Node::of($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;
        $values = $object->toList();

        /** @var mixed $value */
        foreach ($values as $value) {
            if (\is_object($value)) {
                $nodes = $visit($nodes, $value, $visit);
                $valueNode = $nodes->get($value)->match(
                    static fn($node) => $node,
                    static fn() => throw new \LogicException,
                );

                $node->relate(Relation::of(
                    new Relation\Property((string) $i),
                    $valueNode,
                ));
            }

            ++$i;
        }

        /** @var Map<object, Node> */
        return $nodes;
    }
}
