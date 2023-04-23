<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Relation\Property,
    Node\Reference,
};

final class Relation
{
    private Property $property;
    private Reference $reference;

    private function __construct(Property $property, Reference $reference)
    {
        $this->property = $property;
        $this->reference = $reference;
    }

    public static function of(Property $property, Reference $reference): self
    {
        return new self($property, $reference);
    }

    public function property(): Property
    {
        return $this->property;
    }

    public function reference(): Reference
    {
        return $this->reference;
    }

    public function refersTo(object $object): bool
    {
        return $this->reference->equals(Reference::of($object));
    }
}
