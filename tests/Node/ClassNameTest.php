<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\{
    Node\ClassName,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase
{
    public function testInterface()
    {
        $class = new ClassName('foo');

        $this->assertSame('foo', (string) $class);
    }

    public function testThrowWhenEmptyClass()
    {
        $this->expectException(DomainException::class);

        new ClassName('');
    }
}
