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
use Innmind\Filesystem\File\Content;
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

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotEmpty($dot->toString());
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

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotEmpty($dot->toString());
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

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotEmpty($dot->toString());
    }

    public function testClusterize()
    {
        $clusterize = new ByNamespace(
            Map::of(
                [NamespacePattern::of(Foo::class), 'foo'],
                [NamespacePattern::of(Bar::class), 'bar'],
            ),
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

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotEmpty($dot->toString());
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
        $node->relations()->find(static fn() => true)->match(
            static fn($relation) => $relation->node()->highlight(),
            static fn() => null,
        );

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotEmpty($dot->toString());
        $this->assertStringContainsString('#00ff00', $dot->toString());
        $this->assertSame(3, \substr_count($dot->toString(), '#00ff00')); // root + highlighted
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
        $node->relations()->find(static fn() => true)->match(
            static fn($relation) => $relation->highlight(),
            static fn() => null,
        );

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotEmpty($dot->toString());
        $this->assertStringContainsString('#00ff00', $dot->toString());
        $this->assertSame(2, \substr_count($dot->toString(), '#00ff00')); // root + edge
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
        $node->relations()->find(static fn() => true)->match(
            static fn($relation) => $relation->node()->flagAsDependent(),
            static fn() => null,
        );

        $dot = (new Visualize)($node);

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotEmpty($dot->toString());
        $this->assertStringContainsString('#00b6ff', $dot->toString());
        $this->assertSame(1, \substr_count($dot->toString(), '#00b6ff')); // a
    }
}
