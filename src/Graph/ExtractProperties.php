<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Node,
    Relation,
};
use Innmind\Reflection\{
    ReflectionClass,
    Extract,
};
use Innmind\Immutable\Map;

final class ExtractProperties implements Visit
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

        $node = new Node($object);
        $nodes = ($nodes)($object, $node);

        $properties = ReflectionClass::of(\get_class($object))
            ->properties()
            ->map(static fn($property) => $property->name());
        $properties = (new Extract)($object, $properties)->match(
            static fn($properties) => $properties,
            static fn() => throw new \LogicException,
        );

        /**
         * @psalm-suppress MissingClosureReturnType
         * @psalm-suppress MixedArgumentTypeCoercion
         * @var Map<object, Node>
         */
        return $properties
            ->map(static function(string $_, $value) {
                if (\is_array($value)) {
                    return new \ArrayObject($value);
                }

                return $value;
            })
            ->filter(static fn(string $_, $value): bool => \is_object($value))
            ->reduce(
                $nodes,
                static function(Map $nodes, string $property, object $value) use ($visit, $node): Map {
                    $nodes = $visit($nodes, $value, $visit);
                    $valueNode = $nodes->get($value)->match(
                        static fn($node) => $node,
                        static fn() => throw new \LogicException,
                    );

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
