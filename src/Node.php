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
}
