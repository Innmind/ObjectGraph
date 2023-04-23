<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node\ClassName,
    Node\Reference,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;

/**
 * @psalm-immutable
 */
final class Node
{
    private ClassName $class;
    private Reference $reference;
    private Url $location;
    /** @var Set<Relation> */
    private Set $relations;

    /**
     * @param Set<Relation> $relations
     */
    private function __construct(object $object, Set $relations)
    {
        /** @psalm-suppress ImpureMethodCall */
        $file = (new \ReflectionObject($object))->getFileName();

        $this->class = ClassName::of($object);
        $this->reference = Reference::of($object);
        $this->location = Url::of('file://'.$file);
        $this->relations = $relations;
    }

    /**
     * @psalm-pure
     *
     * @param Set<Relation>|null $relations
     */
    public static function of(object $object, Set $relations = null): self
    {
        return new self($object, $relations ?? Set::of());
    }

    public function class(): ClassName
    {
        return $this->class;
    }

    public function reference(): Reference
    {
        return $this->reference;
    }

    public function location(): Url
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
}
