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
}
