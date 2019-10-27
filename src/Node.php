<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node\ClassName,
    Node\Reference,
};
use Innmind\Url\{
    UrlInterface,
    Url,
};
use Innmind\Immutable\{
    Map,
    SetInterface,
    Set,
};

final class Node
{
    private $class;
    private $reference;
    private $location;
    private $relations;
    private $dependency = false;
    private $dependent = false;
    private $highlighted = false;
    private $highlightingPath = false;

    public function __construct(object $object)
    {
        $file = (new \ReflectionObject($object))->getFileName();

        $this->class = new ClassName($object);
        $this->reference = new Reference($object);
        $this->location = Url::fromString('file://'.$file);
        $this->relations = Map::of('string', Relation::class);
    }

    public function relate(Relation $relation): self
    {
        $this->relations = $this->relations->put(
            (string) $relation->property(),
            $relation
        );

        return $this;
    }

    public function class(): ClassName
    {
        return $this->class;
    }

    public function reference(): Reference
    {
        return $this->reference;
    }

    public function location(): UrlInterface
    {
        return $this->location;
    }

    /**
     * @return SetInterface<Relation>
     */
    public function relations(): SetInterface
    {
        return Set::of(Relation::class, ...$this->relations->values());
    }

    public function removeRelations(): void
    {
        $this->relations = $this->relations->clear();
    }

    public function dependsOn(object $dependency): bool
    {
        return $this->relations->values()->reduce(
            false,
            function(bool $isDependent, Relation $relation) use ($dependency): bool {
                return $isDependent || $relation->node()->comesFrom($dependency);
            }
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

        $highlighted = $this
            ->relations
            ->values()
            ->foreach(static function(Relation $relation) use ($object): void {
                $relation->highlightPathTo($object);
            })
            ->filter(static function(Relation $relation): bool {
                return $relation->highlighted();
            });

        if (!$highlighted->empty()) {
            $this->highlight();
        }

        $this->highlightingPath = false;
    }
}
