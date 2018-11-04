<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Visualize,
    Graph,
    Node,
};
use Innmind\Immutable\Str;
use Fixtures\Innmind\ObjectGraph\Foo;
use PHPUnit\Framework\TestCase;

class VisualizeTest extends TestCase
{
    public function testInvokation()
    {
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);
        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Str::class, $dot);
        $expected = <<<DOT

DOT;

        $this->assertSame($expected, (string) $dot);
    }

    public function testRenderRecursiveGraph()
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

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Str::class, $dot);
        $expected = <<<DOT

DOT;

        $this->assertSame($expected, (string) $dot);
    }
}
