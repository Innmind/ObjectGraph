<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Node\ClassName;
use Innmind\Immutable\{
    Map,
    SetInterface,
    Set,
};

final class Node
{
    private $class;
    private $relations;

    public function __construct(object $object)
    {
        $this->class = new ClassName($object);
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

    /**
     * @return SetInterface<Relation>
     */
    public function relations(): SetInterface
    {
        return Set::of(Relation::class, ...$this->relations->values());
    }
}
