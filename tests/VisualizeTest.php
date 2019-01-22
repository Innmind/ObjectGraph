<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Visualize,
    Graph,
    Node,
    NamespacePattern,
    Visitor\FlagDependencies,
    Clusterize\ByNamespace,
};
use Innmind\Stream\Readable;
use Innmind\Immutable\Map;
use Fixtures\Innmind\ObjectGraph\{
    Foo,
    Bar,
};
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
        (new FlagDependencies($leaf))($node);

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Readable::class, $dot);
        $this->assertNotEmpty((string) $dot);
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

        $this->assertInstanceOf(Readable::class, $dot);
        $this->assertNotEmpty((string) $dot);
    }

    public function testClusterize()
    {
        $clusterize = new ByNamespace(
            Map::of(NamespacePattern::class, 'string')
                (new NamespacePattern(Foo::class), 'foo')
                (new NamespacePattern(Bar::class), 'bar')
        );

        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph(new Bar($root));

        $dot = (new Visualize(null, $clusterize))($node);

        $this->assertInstanceOf(Readable::class, $dot);
        $this->assertNotEmpty((string) $dot);
    }
}
