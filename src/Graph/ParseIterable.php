<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\{
    Map,
    Pair,
};

final class ParseIterable implements Visit
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

        if (!\is_iterable($object)) {
            /** @var Map<object, Node> */
            return $nodes;
        }

        $node = Node::of($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;

        /**
         * @var mixed $key
         * @var mixed $value
         */
        foreach ($object as $key => $value) {
            if (\is_object($key)) {
                $nodes = $visit($nodes, $key, $visit);
                $keyNode = $nodes->get($key)->match(
                    static fn($node) => $node,
                    static fn() => throw new \LogicException,
                );

                $node->relate(Relation::of(
                    Relation\Property::of("key[$i]"),
                    $keyNode,
                ));
            }

            if (\is_object($value)) {
                $nodes = $visit($nodes, $value, $visit);
                $valueNode = $nodes->get($value)->match(
                    static fn($node) => $node,
                    static fn() => throw new \LogicException,
                );

                $node->relate(Relation::of(
                    Relation\Property::of("value[$i]"),
                    $valueNode,
                ));
            }

            ++$i;
        }

        /** @var Map<object, Node> */
        return $nodes;
    }
}
