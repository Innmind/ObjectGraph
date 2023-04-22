<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Clusterize;

use Innmind\ObjectGraph\{
    Clusterize\ByNamespace,
    Clusterize,
    NamespacePattern,
    Node,
};
use Innmind\Graphviz;
use Innmind\Immutable\{
    Map,
    Set,
};
use Fixtures\Innmind\ObjectGraph\{
    Foo,
    Bar,
    Baz,
};
use PHPUnit\Framework\TestCase;

class ByNamespaceTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Clusterize::class,
            new ByNamespace(Map::of()),
        );
    }

    public function testInvokation()
    {
        $clusterize = new ByNamespace(
            Map::of()
                (new NamespacePattern(Foo::class), 'foo')
                (new NamespacePattern(Bar::class), 'bar')
                (new NamespacePattern(Baz::class), 'baz')
                (new NamespacePattern(Free::class), 'free'),
        );

        $baz = new Baz(
            $bar = new Bar(
                $foo = new Foo(
                    $foo2 = new Foo,
                    $foo3 = new Foo,
                ),
            ),
        );

        $clusters = $clusterize(
            Map::of()
                (new Node($baz), $baz = Graphviz\Node::named('baz'))
                (new Node($bar), $bar = Graphviz\Node::named('bar'))
                (new Node($foo), $foo = Graphviz\Node::named('foo'))
                (new Node($foo2), $foo2 = Graphviz\Node::named('foo2'))
                (new Node($foo3), $foo3 = Graphviz\Node::named('foo3')),
        );

        $this->assertInstanceOf(Set::class, $clusters);
        $this->assertCount(3, $clusters);
        $cluster = $clusters->find(static fn() => true)->match(
            static fn($cluster) => $cluster,
            static fn() => null,
        );
        $this->assertSame('foo', $cluster->name()->toString());
        $this->assertCount(3, $cluster->roots());
        $roots = $cluster->roots()->toList();
        $this->assertSame('foo', \current($roots)->name()->toString());
        $this->assertNotSame($foo, \current($roots));
        \next($roots);
        $this->assertSame('foo2', \current($roots)->name()->toString());
        $this->assertNotSame($foo2, \current($roots));
        \next($roots);
        $this->assertSame('foo3', \current($roots)->name()->toString());
        $this->assertNotSame($foo3, \current($roots));
        $clusters = $clusters->toList();
        \next($clusters);
        $cluster = \current($clusters);
        $this->assertSame('bar', $cluster->name()->toString());
        $this->assertCount(1, $cluster->roots());
        $roots = $cluster->roots()->toList();
        $this->assertSame('bar', \current($roots)->name()->toString());
        $this->assertNotSame($bar, \current($roots));
        \next($clusters);
        $cluster = \current($clusters);
        $this->assertSame('baz', $cluster->name()->toString());
        $this->assertCount(1, $cluster->roots());
        $roots = $cluster->roots();
        $this->assertSame(
            'baz',
            $roots->find(static fn() => true)->match(
                static fn($node) => $node->name()->toString(),
                static fn() => null,
            ),
        );
        $this->assertNotSame($baz, $roots->find(static fn() => true)->match(
            static fn($node) => $node,
            static fn() => null,
        ));
    }

    public function testClustersInstancesAreNotReused()
    {
        $clusterize = new ByNamespace(
            Map::of()
                (new NamespacePattern(Foo::class), 'foo')
                (new NamespacePattern(Bar::class), 'bar')
                (new NamespacePattern(Baz::class), 'baz')
                (new NamespacePattern(Free::class), 'free'),
        );

        $baz = new Baz(
            $bar = new Bar(
                $foo = new Foo(
                    $foo2 = new Foo,
                    $foo3 = new Foo,
                ),
            ),
        );

        $clusters = $clusterize(
            $nodes = Map::of()
                (new Node($baz), Graphviz\Node::named('baz'))
                (new Node($bar), Graphviz\Node::named('bar'))
                (new Node($foo), Graphviz\Node::named('foo'))
                (new Node($foo2), Graphviz\Node::named('foo2'))
                (new Node($foo3), Graphviz\Node::named('foo3')),
        );
        $clusters2 = $clusterize($nodes);

        $this->assertFalse($clusters->equals($clusters2));
    }
}
