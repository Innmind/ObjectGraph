<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\{
    Node\ClassName,
    NamespacePattern,
};
use PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase
{
    public function testInterface()
    {
        $class = new ClassName(new \stdClass);

        $this->assertSame('stdClass', (string) $class);
    }

    public function testIn()
    {
        $class = new ClassName($this);

        $this->assertTrue($class->in(new NamespacePattern('Tests\\Innmind\ObjectGraph')));
        $this->assertFalse($class->in(new NamespacePattern('Innmind\ObjectGraph')));
    }
}
