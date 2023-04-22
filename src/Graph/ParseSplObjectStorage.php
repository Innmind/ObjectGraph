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

final class ParseSplObjectStorage implements Visit
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

        if (!$object instanceof \SplObjectStorage) {
            /** @var Map<object, Node> */
            return $nodes;
        }

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;

        foreach ($object as $key) {
            /** @var mixed */
            $value = $object[$key];

            $nodes = $visit($nodes, $key, $visit);
            $keyNode = $nodes->get($key)->match(
                static fn($node) => $node,
                static fn() => throw new \LogicException,
            );

            $node->relate(new Relation(
                new Relation\Property("key[$i]"),
                $keyNode,
            ));

            if (\is_object($value)) {
                $nodes = $visit($nodes, $value, $visit);
                $valueNode = $nodes->get($value)->match(
                    static fn($node) => $node,
                    static fn() => throw new \LogicException,
                );

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
