<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\Reflection\{
    ReflectionClass,
    ReflectionObject,
    ExtractionStrategy\ReflectionStrategy,
};
use Innmind\Immutable\Map;

final class Graph
{
    private $nodes;

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

        $properties = ReflectionClass::of(\get_class($object))->properties();

        $properties = ReflectionObject::of(
            $object,
            null,
            null,
            new ReflectionStrategy
        )
            ->extract(...$properties)
            ->filter(static function(string $property, $value): bool {
                return \is_object($value);
            });

        $node = new Node($object);
        $this->nodes = $this->nodes->put($object, $node);
        $node = $properties->reduce(
            $node,
            function(Node $node, string $property, object $value): Node {
                return $node->relate(new Relation(
                    new Relation\Property($property),
                    $this->visit($value)
                ));
            }
        );

        return $node;
    }
}
