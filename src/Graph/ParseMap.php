<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Immutable\Map;

final class ParseMap implements Visit
{
    /**
     * @param Map<object, Node> $nodes
     *
     * @return Map<object, Node>
     */
    public function __invoke(
        Map $nodes,
        object $object,
        Visit $visit
    ): Map {
        if ($nodes->contains($object)) {
            /** @var Map<object, Node> */
            return $nodes;
        }

        if (!$object instanceof Map) {
            /** @var Map<object, Node> */
            return $nodes;
        }

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;
        $values = new \ArrayObject;
        $object->foreach(static fn($key, $value) => $values->append([$key, $value]));

        /** @var array{0: mixed, 1: mixed} $pair */
        foreach ($values as $pair) {
            /**
             * @var mixed $key
             * @var mixed $value
             */
            [$key, $value] = $pair;

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
