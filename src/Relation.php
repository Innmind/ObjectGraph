<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\Relation\Property;

final class Relation
{
    private Property $property;
    private Node $node;
    private bool $highlighted = false;

    private function __construct(Property $property, Node $node)
    {
        $this->property = $property;
        $this->node = $node;
    }

    public static function of(Property $property, Node $node): self
    {
        return new self($property, $node);
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

    public function highlightPathTo(object $object): void
    {
        $this->node->highlightPathTo($object);

        if ($this->node->highlighted()) {
            $this->highlight();
        }
    }
}
