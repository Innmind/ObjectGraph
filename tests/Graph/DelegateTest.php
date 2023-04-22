<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Graph;

use Innmind\ObjectGraph\{
    Graph\Delegate,
    Graph\Visit,
    Node,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class DelegateTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Visit::class, new Delegate);
    }

    public function testInvokation()
    {
        $delegate = new Delegate(
            $inner1 = $this->createMock(Visit::class),
            $inner2 = $this->createMock(Visit::class),
            $inner3 = $this->createMock(Visit::class),
        );
        $object = new \stdClass;
        $nodes = Map::of();
        $inner1
            ->expects($this->once())
            ->method('__invoke')
            ->with($nodes, $object, $delegate)
            ->willReturn($nodes1 = Map::of());
        $inner2
            ->expects($this->once())
            ->method('__invoke')
            ->with($nodes1, $object, $delegate)
            ->willReturn($nodes2 = Map::of());
        $inner3
            ->expects($this->once())
            ->method('__invoke')
            ->with($nodes2, $object, $delegate)
            ->willReturn($nodes3 = Map::of());

        $this->assertSame($nodes3, $delegate($nodes, $object, $this->createMock(Visit::class)));
    }
}
