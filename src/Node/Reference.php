<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

/**
 * @psalm-immutable
 */
final class Reference
{
    private string $value;

    private function __construct(object $object)
    {
        $this->value = \spl_object_hash($object);
    }

    /**
     * @psalm-pure
     */
    public static function of(object $object): self
    {
        return new self($object);
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
