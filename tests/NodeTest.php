<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node,
    Node\Reference,
    Relation,
    Relation\Property,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testInterface()
    {
        $node = Node::of(
            $object = new class {
            },
            Set::of($relation = Relation::of(
                Property::of('bar'),
                Reference::of(new \stdClass),
            )),
        );

        $this->assertInstanceOf(Set::class, $node->relations());
        $this->assertInstanceOf(Url::class, $node->location());
        $this->assertSame('file://'.__FILE__, $node->location()->toString());
        $this->assertCount(1, $node->relations());
        $this->assertSame([$relation], $node->relations()->toList());
        $this->assertTrue($node->comesFrom($object));
        $this->assertFalse($node->comesFrom(new class {
        }));
    }

    public function testDependsOn()
    {
        $object = new class {
            public $foo;
        };
        $dependency = new class {
        };
        $object->foo = $dependency;

        $node = Node::of($object, Set::of(Relation::of(
            Property::of('foo'),
            Reference::of($dependency),
        )));

        $this->assertTrue($node->dependsOn($dependency));
        $this->assertFalse($node->dependsOn(new class {
        }));
    }
}
