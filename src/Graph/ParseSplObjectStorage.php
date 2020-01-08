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
            $pair = new Pair($key, $value);
            $nodes = $visit($nodes, $pair, $visit);

            $node->relate(new Relation(
                new Relation\Property((string) $i),
                $nodes->get($pair),
            ));
            ++$i;
        }

        return $nodes;
    }
}
