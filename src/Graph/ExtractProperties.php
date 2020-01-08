<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Reflection\{
    ReflectionClass,
    ReflectionObject,
    ExtractionStrategy\ReflectionStrategy,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;

final class ExtractProperties implements Visit
{
    /**
     * @var Map<object, Node> $nodes
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

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $properties = ReflectionClass::of(\get_class($object))->properties();

        $properties = ReflectionObject::of(
            $object,
            null,
            null,
            new ReflectionStrategy,
        )
            ->extract(...unwrap($properties));

        /**
         * @psalm-suppress MissingClosureReturnType
         * @var Map<object, Node>
         */
        return $properties
            ->map(static function(string $property, $value) {
                if (\is_array($value)) {
                    return new \ArrayObject($value);
                }

                return $value;
            })
            ->filter(fn(string $property, $value): bool => \is_object($value))
            ->reduce(
                $nodes,
                static function(Map $nodes, string $property, object $value) use ($visit, $node): Map {
                    $nodes = $visit($nodes, $value, $visit);
                    $valueNode = $nodes->get($value);

                    if ($value instanceof \ArrayObject && $valueNode->relations()->empty()) {
                        return $nodes;
                    }

                    $node->relate(new Relation(
                        new Relation\Property($property),
                        $valueNode,
                    ));

                    return $nodes;
                },
            );
    }
}
