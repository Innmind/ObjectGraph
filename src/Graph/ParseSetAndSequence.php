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
use function Innmind\Immutable\unwrap;

final class ParseSetAndSequence implements Visit
{
    public function __invoke(
        Map $nodes,
        object $object,
        Visit $visit
    ): Map {
        if ($nodes->contains($object)) {
            return $nodes;
        }

        if (!$object instanceof Set && !$object instanceof Sequence) {
            return $nodes;
        }

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $i = 0;
        $values = unwrap($object);

        foreach ($values as $value) {
            if (\is_object($value)) {
                $nodes = $visit($nodes, $value, $visit);
                $valueNode = $nodes->get($value);

                $node->relate(new Relation(
                    new Relation\Property((string) $i),
                    $valueNode,
                ));
            }

            ++$i;
        }

        return $nodes;
    }
}
