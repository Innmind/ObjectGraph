<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\Node;

/**
 * @psalm-immutable
 */
final class ClassName
{
    /** @var class-string */
    private string $value;

    private function __construct(object $object)
    {
        $this->value = \get_class($object);
    }

    /**
     * @psalm-pure
     */
    public static function of(object $object): self
    {
        return new self($object);
    }

    /**
     * @return class-string
     */
    public function toString(): string
    {
        return $this->value;
    }
}
