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

        $this->assertSame('stdClass', $class->toString());
    }

    public function testIn()
    {
        $class = new ClassName($this);

        $this->assertTrue($class->in(NamespacePattern::of('Tests\\Innmind\ObjectGraph')));
        $this->assertFalse($class->in(NamespacePattern::of('Innmind\ObjectGraph')));
    }
}
