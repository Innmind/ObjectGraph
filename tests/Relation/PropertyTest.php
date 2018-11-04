<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Relation;

use Innmind\ObjectGraph\{
    Relation\Property,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testInterface()
    {
        $class = new Property('foo');

        $this->assertSame('foo', (string) $class);
    }

    public function testThrowWhenEmptyClass()
    {
        $this->expectException(DomainException::class);

        new Property('');
    }
}
