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
    LocationRewriter\SublimeHandler,
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
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
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
        // a <-----|
        //  |-> b -|
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

    public function testRewriteLocation()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);

        $dot = (new Visualize(new SublimeHandler))($node);

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

        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
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

    public function testHighlight()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);
        $node->relations()->current()->node()->highlight();

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Readable::class, $dot);
        $this->assertNotEmpty((string) $dot);
        $this->assertStringContainsString('#00ff00', (string) $dot);
        $this->assertSame(3, \substr_count((string) $dot, '#00ff00')); // root + highlighted
    }

    public function testHighlightRelation()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);
        $node->relations()->current()->highlight();

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Readable::class, $dot);
        $this->assertNotEmpty((string) $dot);
        $this->assertStringContainsString('#00ff00', (string) $dot);
        $this->assertSame(2, \substr_count((string) $dot, '#00ff00')); // root + edge
    }

    public function testRenderDependent()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $graph = new Graph;
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $node = $graph($root);
        $node->relations()->current()->node()->flagAsDependent();

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Readable::class, $dot);
        $this->assertNotEmpty((string) $dot);
        $this->assertStringContainsString('#00b6ff', (string) $dot);
        $this->assertSame(1, \substr_count((string) $dot, '#00b6ff')); // a
    }
}
