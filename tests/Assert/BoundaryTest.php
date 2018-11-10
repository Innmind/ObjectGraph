<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Assert\Boundary,
    Graph,
};
use Fixtures\Innmind\ObjectGraph\{
    Foo,
    Bar,
    Baz,
    Free,
    Indirection,
};
use PHPUnit\Framework\TestCase;

class BoundaryTest extends TestCase
{
    public function testBoundaryRespected()
    {
        $boundary = Boundary::of(
            Baz::class,
            'Innmind\\ObjectGraph',
            'stdClass'
        );
        $object = new Baz(
            new Bar(
                new Foo
            )
        );

        $this->assertTrue($boundary(
            (new Graph)($object)
        ));
    }

    public function testBoundaryNotRespected()
    {
        $boundary = Boundary::of(
            Free::class,
            'stdClass'
        );
        $object = new Free(
            new Indirection(
                new \stdClass
            )
        );

        $this->assertFalse($boundary(
            (new Graph)($object)
        ));
    }
}
