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

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;

        /**
         * @var mixed $key
         * @var mixed $value
         */
        foreach ($object as $key => $value) {
            if (\is_object($key)) {
                $nodes = $visit($nodes, $key, $visit);
                $keyNode = $nodes->get($key);

                $node->relate(new Relation(
                    new Relation\Property("key[$i]"),
                    $keyNode,
                ));
            }

            if (\is_object($value)) {
                $nodes = $visit($nodes, $value, $visit);
                $valueNode = $nodes->get($value);

                $node->relate(new Relation(
                    new Relation\Property("value[$i]"),
                    $valueNode,
                ));
            }

            ++$i;
        }

        /** @var Map<object, Node> */
        return $nodes;
    }
}
