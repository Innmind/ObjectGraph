<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Graph,
    Node,
};
use Fixtures\Innmind\ObjectGraph\Foo;
use PHPUnit\Framework\TestCase;

class GraphTest extends TestCase
{
    public function testInvokation()
    {
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);

        $this->assertInstanceOf(Node::class, $node);
        $this->assertCount(2, $node->relations());
        [$a, $b] = $node->relations()->toPrimitive();
        $this->assertSame('a', (string) $a->property());
        $this->assertSame(Foo::class, (string) $a->node()->class());
        $this->assertSame('b', (string) $b->property());
        $this->assertSame(Foo::class, (string) $b->node()->class());
        $this->assertCount(1, $a->node()->relations());
        $this->assertCount(1, $b->node()->relations());
        $this->assertSame(
            $a->node()->relations()->current()->node(),
            $b->node()->relations()->current()->node()
        );
    }

    public function testCyclicGraph()
    {
        $graph = new Graph;

        $a = new class {
            public $foo;
        };
        $b = new class {
            public $bar;
        };
        $a->foo = $b;
        $b->bar = $a;

        $node = $graph($a);

        $this->assertCount(1, $node->relations());
        $this->assertSame('foo', (string) $node->relations()->current()->property());
        $this->assertSame(
            $node,
            $node->relations()->current()->node()->relations()->current()->node()
        );
    }
}
