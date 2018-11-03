<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Node\ClassName;
use Innmind\Immutable\Map;

final class Node
{
    private $class;
    private $relations;

    public function __construct(object $object)
    {
        $this->class = new ClassName(\get_class($object));
        $this->relations = Map::of('string', Relation::class);
    }

    public function relate(Relation $relation): void
    {
        $this->relations = $this->relations->put(
            (string) $relation->property(),
            $relation
        );
    }
}
