<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Relation\Property;

final class Relation
{
    private $property;
    private $node;
    private $highlighted = false;

    public function __construct(Property $property, Node $node)
    {
        $this->property = $property;
        $this->node = $node;
    }

    public function property(): Property
    {
        return $this->property;
    }

    public function node(): Node
    {
        return $this->node;
    }

    public function highlight(): void
    {
        $this->highlighted = true;
    }

    public function highlighted(): bool
    {
        return $this->highlighted;
    }
}
