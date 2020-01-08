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
    public function __invoke(
        Map $nodes,
        object $object,
        Visit $visit
    ): Map {
        if ($nodes->contains($object)) {
            return $nodes;
        }

        if (!$object instanceof \SplObjectStorage) {
            return $nodes;
        }

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;

        foreach ($object as $key) {
            $value = $object[$key];

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

        return $nodes;
    }
}
