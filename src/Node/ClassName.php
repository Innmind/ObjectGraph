<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

final class ClassName
{
    private $value;

    public function __construct(object $object)
    {
        $this->value = \get_class($object);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
