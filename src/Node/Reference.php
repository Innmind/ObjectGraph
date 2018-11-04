<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

final class Reference
{
    private $value;

    public function __construct(object $object)
    {
        $this->value = \spl_object_hash($object);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
