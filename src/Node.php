<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node\ClassName,
    Node\Reference,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    Set,
};

final class Node
{
    private ClassName $class;
    private Reference $reference;
    private Url $location;
    /** @var Map<string, Relation> */
    private Map $relations;
    private bool $dependency = false;
    private bool $dependent = false;
    private bool $highlighted = false;
    private bool $highlightingPath = false;

    public function __construct(object $object)
    {
        $file = (new \ReflectionObject($object))->getFileName();

        $this->class = new ClassName($object);
        $this->reference = new Reference($object);
        $this->location = Url::of('file://'.$file);
        /** @var Map<string, Relation> */
        $this->relations = Map::of();
    }

    public function relate(Relation $relation): void
    {
        $this->relations = ($this->relations)(
            $relation->property()->toString(),
            $relation,
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

    public function location(): Url
    {
        return $this->location;
    }

    /**
     * @return Set<Relation>
     */
    public function relations(): Set
    {
        return $this->relations->values()->toSet();
    }

    public function removeRelations(): void
    {
        $this->relations = $this->relations->clear();
    }

    public function dependsOn(object $dependency): bool
    {
        return $this->relations->values()->reduce(
            false,
            static function(bool $isDependent, Relation $relation) use ($dependency): bool {
                return $isDependent || $relation->node()->comesFrom($dependency);
            },
        );
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
        return $this->reference->equals(new Reference($object));
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
            ->values()
            ->foreach(static function(Relation $relation) use ($object): void {
                $relation->highlightPathTo($object);
            });
        $highlighted = $this->relations->values()
            ->filter(static function(Relation $relation): bool {
                return $relation->highlighted();
            });

        if (!$highlighted->empty()) {
            $this->highlight();
        }

        $this->highlightingPath = false;
    }
}
