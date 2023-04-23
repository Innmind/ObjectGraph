<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Relation\Property,
    Node\Reference,
};
use Innmind\Reflection\{
    ReflectionClass,
    Extract,
};
use Innmind\Immutable\{
    Map,
    Set,
};

final class Flatten
{
    private function __construct()
    {
    }

    public function __invoke(object $object): Graph
    {
        $nodes = self::visit(Map::of(), $object)
            ->map(static fn($object, $properties) => Node::of(
                $object,
                $properties
                    ->map(static fn($name, $object) => Relation::of(
                        Property::of($name),
                        Reference::of($object),
                    ))
                    ->values()
                    ->toSet(),
            ))
            ->values()
            ->toSet();

        return $nodes->find(static fn($node) => $node->comesFrom($object))->match(
            static fn($root) => Graph::of($root, $nodes),
            static fn() => throw new \LogicException('Root node not found'),
        );
    }

    public static function of(): self
    {
        return new self;
    }

    /**
     * @param Map<object, Map<non-empty-string, object>> $found
     *
     * @return Map<object, Map<non-empty-string, object>>
     */
    private static function visit(Map $found, object $object): Map
    {
        return $found->get($object)->match(
            static fn() => $found, // to avoid infinite recursion on cyclic graph
            static fn() => self::lookup($found, $object),
        );
    }

    /**
     * @param Map<object, Map<non-empty-string, object>> $found
     *
     * @return Map<object, Map<non-empty-string, object>>
     */
    private static function lookup(Map $found, object $object): Map
    {
        $properties = ReflectionClass::of($object::class)
            ->properties()
            ->map(static fn($property) => $property->name());

        return (new Extract)($object, $properties)
            ->map(static fn($properties) => $properties->map(
                static fn($_, $value) => match (true) {
                    \is_array($value) => new \ArrayObject($value),
                    default => $value,
                },
            ))
            ->map(self::keepObjects(...))
            ->map(
                static fn($properties) => $properties
                    ->values()
                    ->reduce(
                        ($found)($object, $properties),
                        self::visit(...),
                    ),
            )
            ->match(
                static fn($found) => $found,
                static fn() => $found,
            );
    }

    /**
     * @param Map<non-empty-string, mixed> $properties
     *
     * @return Map<non-empty-string, object>
     */
    private static function keepObjects(Map $properties): Map
    {
        /** @var Map<non-empty-string, object> */
        return $properties->filter(
            static fn($_, $value) => \is_object($value),
        );
    }
}
