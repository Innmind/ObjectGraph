<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node\ClassName,
    Node\Reference,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;

final class Node
{
    private ClassName $class;
    private Reference $reference;
    private Url $location;
    /** @var Set<Relation> */
    private Set $relations;
    private bool $dependency = false;
    private bool $dependent = false;
    private bool $highlighted = false;
    private bool $highlightingPath = false;

    /**
     * @param Set<Relation> $relations
     */
    private function __construct(object $object, Set $relations)
    {
        $file = (new \ReflectionObject($object))->getFileName();

        $this->class = ClassName::of($object);
        $this->reference = Reference::of($object);
        $this->location = Url::of('file://'.$file);
        $this->relations = $relations;
    }

    /**
     * @param Set<Relation>|null $relations
     */
    public static function of(object $object, Set $relations = null): self
    {
        return new self($object, $relations ?? Set::of());
    }

    public function relate(Relation $relation): void
    {
        $this->relations = $this
            ->relations
            ->filter(static fn($known) => $known->property()->toString() !== $relation->property()->toString())
            ->add($relation);
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

    public function removeRelations(): void
    {
        $this->relations = $this->relations->clear();
    }

    public function dependsOn(object $dependency): bool
    {
        return $this->relations->any(static fn($relation) => $relation->node()->comesFrom($dependency));
    }

    public function flagAsDependent(): void
    {
        $this->dependent = true;
    }

    public function isDependent(): bool
    {
        return $this->dependent;
    }

    public function comesFrom(object $object): bool
    {
        return $this->reference->equals(Reference::of($object));
    }

    public function flagAsDependency(): void
    {
        $this->dependency = true;
    }

    public function isDependency(): bool
    {
        return $this->dependency;
    }

    public function highlight(): void
    {
        $this->highlighted = true;
    }

    public function highlighted(): bool
    {
        return $this->highlighted;
    }

    public function highlightPathTo(object $object): void
    {
        if ($this->highlightingPath) {
            return;
        }

        if ($this->comesFrom($object)) {
            $this->highlight();

            return;
        }

        $this->highlightingPath = true;

        $_ = $this
            ->relations
            ->foreach(static function(Relation $relation) use ($object): void {
                $relation->highlightPathTo($object);
            });
        $highlighted = $this
            ->relations
            ->filter(static function(Relation $relation): bool {
                return $relation->highlighted();
            });

        if (!$highlighted->empty()) {
            $this->highlight();
        }

        $this->highlightingPath = false;
    }
}
