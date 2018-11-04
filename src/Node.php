<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node\ClassName,
    Node\Reference,
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
    private $relations;

    public function __construct(object $object)
    {
        $this->class = new ClassName($object);
        $this->reference = new Reference($object);
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

    /**
     * @return SetInterface<Relation>
     */
    public function relations(): SetInterface
    {
        return Set::of(Relation::class, ...$this->relations->values());
    }
}
