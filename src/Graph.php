<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Reflection\{
    ReflectionClass,
    ReflectionObject,
    ExtractionStrategy\ReflectionStrategy,
};
use Innmind\Immutable\{
    Map,
    Pair,
};
use function Innmind\Immutable\unwrap;

final class Graph
{
    private ?Map $nodes = null;

    public function __invoke(object $root): Node
    {
        try {
            $this->nodes = Map::of('object', Node::class);

            return $this->visit($root);
        } finally {
            $this->nodes = null;
        }
    }

    private function visit(object $object): Node
    {
        if ($this->nodes->contains($object)) {
            return $this->nodes->get($object);
        }

        $node = new Node($object);
        $this->nodes = $this->nodes->put($object, $node);

        $properties = ReflectionClass::of(\get_class($object))->properties();

        $properties = ReflectionObject::of(
            $object,
            null,
            null,
            new ReflectionStrategy,
        )
            ->extract(...unwrap($properties));

        $iterables = $properties->filter(static function(string $property, $value): bool {
            return \is_iterable($value);
        });
        $objects = $properties
            ->filter(static function(string $property, $value): bool {
                return \is_object($value);
            })
            ->filter(static function(string $property) use ($iterables): bool {
                return !$iterables->contains($property); // because an iterable can be an object
            });

        $node = $this->visitIterables($node, $iterables);

        return $this->visitObjects($node, $objects);
    }

    /**
     * @param Map<string, iterable> $properties
     */
    private function visitIterables(Node $node, Map $properties): Node
    {
        // this function transform any "property:string => iterable" into the
        // format "property:string ArrayObject<Innmind\Immutable\Pair>" so it
        // can respect the fact that a property is referenced only once in a node
        // it as the side effect of modifying the true nature of the iterable
        // but it is the only solution to adapt to any types of iterables
        return $properties->reduce(
            $node,
            function(Node $node, string $property, iterable $iterable): Node {
                $pairs = Map::of('int', Pair::class);

                foreach ($iterable as $key => $value) {
                    $pairs = $pairs->put(
                        $pairs->size(), // index of the pair
                        new Pair($key, $value),
                    );
                }

                $collection = $pairs->reduce(
                    new Node(new \ArrayObject),
                    function(Node $collection, int $index, Pair $pair): Node {
                        $node = $this->visit($pair);

                        if ($node->relations()->size() === 0) {
                            // do not add the pair in the graph if it doesn't
                            // contain any object
                            return $collection;
                        }

                        return $collection->relate(new Relation(
                            new Relation\Property((string) $index),
                            $node,
                        ));
                    },
                );

                if ($collection->relations()->size() === 0) {
                    // do not add the collection to the graph if it doesn't
                    // contain any object
                    return $node;
                }

                return $node->relate(new Relation(
                    new Relation\Property($property),
                    $collection,
                ));
            },
        );
    }

    /**
     * @param  Map<string, object> $properties
     */
    private function visitObjects(Node $node, Map $properties): Node
    {
        return $properties->reduce(
            $node,
            function(Node $node, string $property, object $value): Node {
                return $node->relate(new Relation(
                    new Relation\Property($property),
                    $this->visit($value),
                ));
            },
        );
    }
}
