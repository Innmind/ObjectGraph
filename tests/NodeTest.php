<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node,
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
        $node = Node::of($object = new class {
        });

        $this->assertInstanceOf(Set::class, $node->relations());
        $this->assertInstanceOf(Url::class, $node->location());
        $this->assertSame('file://'.__FILE__, $node->location()->toString());
        $this->assertCount(0, $node->relations());
        $this->assertNull($node->relate($relation = Relation::of(
            Property::of('bar'),
            Node::of(new \stdClass),
        )));
        $this->assertCount(1, $node->relations());
        $this->assertSame([$relation], $node->relations()->toList());
        $this->assertNull($node->removeRelations());
        $this->assertCount(0, $node->relations());
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

        $node = Node::of($object);
        $node->relate(Relation::of(
            Property::of('foo'),
            Node::of($dependency),
        ));

        $this->assertTrue($node->dependsOn($dependency));
        $this->assertFalse($node->dependsOn(new class {
        }));
    }
}
