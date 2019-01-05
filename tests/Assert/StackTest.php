<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Assert\Stack,
    Graph,
};
use Fixtures\Innmind\ObjectGraph\{
    Foo,
    Bar,
    Baz,
};
use PHPUnit\Framework\TestCase;

class StackTest extends TestCase
{
    public function testStackFound()
    {
        $object = new Baz(
            new Bar(
                new Foo
            )
        );
        $stack = Stack::of(Baz::class, Bar::class, Foo::class);

        $this->assertTrue($stack(
            (new Graph)($object)
        ));

        $stack = Stack::of(Baz::class, Foo::class);

        $this->assertTrue($stack(
            (new Graph)($object)
        ));
    }

    public function testStackNotFound()
    {
        $object = new Baz(
            new Bar(
                new Foo
            )
        );

        $stack = Stack::of(Baz::class, Foo::class, \stdClass::class);

        $this->assertFalse($stack(
            (new Graph)($object)
        ));
    }

    public function testObjectGraphDeeperThanExpectedStack()
    {
        $object = new Baz(
            new Bar(
                new Foo
            )
        );
        $stack = Stack::of(Baz::class, Bar::class);

        $this->assertTrue($stack(
            (new Graph)($object)
        ));

        $stack = Stack::of(Baz::class, Foo::class);

        $this->assertTrue($stack(
            (new Graph)($object)
        ));
    }

    public function testStackFoundInCyclicGraph()
    {
        $a = new class {
            public $foo;
        };
        $b = new class {
            public $bar;
            public $foo;
        };
        $c = new class {
            public $foo;
        };
        $a->foo = $b;
        // this cycle could prevent finding the stack due to infinite recursion
        // ending in a segfault, the infinite recursion occur because "bar" is
        // defined before "foo" in the "b" class
        $b->bar = $a;
        $b->foo = $c;
        $c->foo = $a;

        $stack = Stack::of(get_class($a), get_class($b), get_class($c));

        $this->assertTrue($stack(
            (new Graph)($a)
        ));
    }
}
