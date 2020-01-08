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
    public function __invoke(
        Map $nodes,
        object $object,
        Visit $visit
    ): Map {
        if ($nodes->contains($object)) {
            return $nodes;
        }

        if (!\is_iterable($object)) {
            return $nodes;
        }

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;

        foreach ($object as $key => $value) {
            $pair = new Pair($key, $value);
            $nodes = $visit($nodes, $pair, $visit);
            $pairNode = $nodes->get($pair);

            if (!$pairNode->relations()->empty()) {
                $node->relate(new Relation(
                    new Relation\Property((string) $i),
                    $pairNode,
                ));
            }

            ++$i;
        }

        return $nodes;
    }
}
