<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Assert;

use Innmind\ObjectGraph\{
    Assert\Acyclic,
    Graph,
};
use PHPUnit\Framework\TestCase;

class AcyclicTest extends TestCase
{
    public function testCyclicGraph()
    {
        // a <----------|
        //  |-> b       |
        //       |-> c -|
        $a = new class {
            public $foo;
        };
        $b = new class {
            public $foo;
        };
        $c = new class {
            public $foo;
        };
        $a->foo = $b;
        $b->foo = $c;
        $c->foo = $a;

        $this->assertFalse(
            (new Acyclic)(
                (new Graph)($a)
            ),
        );
    }

    public function testAcyclicGraph()
    {
        // a
        //  |-> b
        //       |-> c
        //            |-> new stdClass
        $a = new class {
            public $foo;
        };
        $b = new class {
            public $foo;
        };
        $c = new class {
            public $foo;
        };
        $a->foo = $b;
        $b->foo = $c;
        $c->foo = new \stdClass;

        $this->assertTrue(
            (new Acyclic)(
                (new Graph)($a)
            ),
        );
    }
}
