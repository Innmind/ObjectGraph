<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node\ClassName,
    Node\Reference,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Node
{
    private ClassName $class;
    private Reference $reference;
    /** @var Maybe<Url> */
    private Maybe $location;
    /** @var Set<Relation> */
    private Set $relations;
    private bool $dependency;

    /**
     * @param Maybe<Url> $location
     * @param Set<Relation> $relations
     */
    private function __construct(
        ClassName $class,
        Reference $reference,
        Maybe $location,
        Set $relations,
        bool $dependency,
    ) {
        $this->class = $class;
        $this->reference = $reference;
        $this->location = $location;
        $this->relations = $relations;
        $this->dependency = $dependency;
    }

    /**
     * @psalm-pure
     *
     * @param Set<Relation>|null $relations
     */
    public static function of(object $object, Set $relations = null): self
    {
        /** @var Maybe<Url> */
        $location = Maybe::nothing();

        return new self(
            ClassName::of($object),
            Reference::of($object),
            $location,
            $relations ?? Set::of(),
            false,
        );
    }

    public function class(): ClassName
    {
        return $this->class;
    }

    public function reference(): Reference
    {
        return $this->reference;
    }

    public function locatedAt(Url $location): self
    {
        return new self(
            $this->class,
            $this->reference,
            Maybe::just($location),
            $this->relations,
            $this->dependency,
        );
    }

    /**
     * @return Maybe<Url>
     */
    public function location(): Maybe
    {
        return $this->location;
    }

    /**
     * @return Set<Relation>
     */
    public function relations(): Set
    {
        return $this->relations;
    }

    public function dependsOn(object $dependency): bool
    {
        return $this->relations->any(static fn($relation) => $relation->refersTo($dependency));
    }

    public function comesFrom(object $object): bool
    {
        return $this->reference->equals(Reference::of($object));
    }

    public function flagAsDependency(): self
    {
        return new self(
            $this->class,
            $this->reference,
            $this->location,
            $this->relations,
            true,
        );
    }

    public function dependency(): bool
    {
        return $this->dependency;
    }
}
