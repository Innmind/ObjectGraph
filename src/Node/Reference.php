<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

final class Reference
{
    private string $value;

    public function __construct(object $object)
    {
        $this->value = \spl_object_hash($object);
    }

    public function equals(self $self): bool
    {
        return $this->value === $self->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
