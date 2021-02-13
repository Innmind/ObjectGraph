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
use function Innmind\Immutable\{
    first,
    unwrap,
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
            new ByNamespace(Map::of(NamespacePattern::class, 'string'))
        );
    }

    public function testThrowWhenInvalidMapKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<Innmind\ObjectGraph\NamespacePattern, string>');

        new ByNamespace(Map::of('string', 'string'));
    }

    public function testThrowWhenInvalidMapValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<Innmind\ObjectGraph\NamespacePattern, string>');

        new ByNamespace(Map::of(NamespacePattern::class, 'object'));
    }

    public function testInvokation()
    {
        $clusterize = new ByNamespace(
            Map::of(NamespacePattern::class, 'string')
                (new NamespacePattern(Foo::class), 'foo')
                (new NamespacePattern(Bar::class), 'bar')
                (new NamespacePattern(Baz::class), 'baz')
                (new NamespacePattern(Free::class), 'free')
        );

        $baz = new Baz(
            $bar = new Bar(
                $foo = new Foo(
                    $foo2 = new Foo,
                    $foo3 = new Foo
                )
            )
        );

        $clusters = $clusterize(
            Map::of(Node::class, Graphviz\Node::class)
                (new Node($baz), $baz = Graphviz\Node\Node::named('baz'))
                (new Node($bar), $bar = Graphviz\Node\Node::named('bar'))
                (new Node($foo), $foo = Graphviz\Node\Node::named('foo'))
                (new Node($foo2), $foo2 = Graphviz\Node\Node::named('foo2'))
                (new Node($foo3), $foo3 = Graphviz\Node\Node::named('foo3'))
        );

        $this->assertInstanceOf(Set::class, $clusters);
        $this->assertSame(Graphviz\Graph::class, $clusters->type());
        $this->assertCount(3, $clusters);
        $cluster = first($clusters);
        $this->assertSame('foo', $cluster->name()->toString());
        $this->assertCount(3, $cluster->roots());
        $roots = unwrap($cluster->roots());
        $this->assertSame('foo', \current($roots)->name()->toString());
        $this->assertNotSame($foo, \current($roots));
        \next($roots);
        $this->assertSame('foo2', \current($roots)->name()->toString());
        $this->assertNotSame($foo2, \current($roots));
        \next($roots);
        $this->assertSame('foo3', \current($roots)->name()->toString());
        $this->assertNotSame($foo3, \current($roots));
        $clusters = unwrap($clusters);
        \next($clusters);
        $cluster = \current($clusters);
        $this->assertSame('bar', $cluster->name()->toString());
        $this->assertCount(1, $cluster->roots());
        $roots = unwrap($cluster->roots());
        $this->assertSame('bar', \current($roots)->name()->toString());
        $this->assertNotSame($bar, \current($roots));
        \next($clusters);
        $cluster = \current($clusters);
        $this->assertSame('baz', $cluster->name()->toString());
        $this->assertCount(1, $cluster->roots());
        $roots = $cluster->roots();
        $this->assertSame('baz', first($roots)->name()->toString());
        $this->assertNotSame($baz, first($roots));
    }

    public function testClustersInstancesAreNotReused()
    {
        $clusterize = new ByNamespace(
            Map::of(NamespacePattern::class, 'string')
                (new NamespacePattern(Foo::class), 'foo')
                (new NamespacePattern(Bar::class), 'bar')
                (new NamespacePattern(Baz::class), 'baz')
                (new NamespacePattern(Free::class), 'free')
        );

        $baz = new Baz(
            $bar = new Bar(
                $foo = new Foo(
                    $foo2 = new Foo,
                    $foo3 = new Foo
                )
            )
        );

        $clusters = $clusterize(
            $nodes = Map::of(Node::class, Graphviz\Node::class)
                (new Node($baz), Graphviz\Node\Node::named('baz'))
                (new Node($bar), Graphviz\Node\Node::named('bar'))
                (new Node($foo), Graphviz\Node\Node::named('foo'))
                (new Node($foo2), Graphviz\Node\Node::named('foo2'))
                (new Node($foo3), Graphviz\Node\Node::named('foo3'))
        );
        $clusters2 = $clusterize($nodes);

        $this->assertFalse($clusters->equals($clusters2));
    }
}
