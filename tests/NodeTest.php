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
        $this->assertFalse($node->isDependency());
        $this->assertNull($node->flagAsDependency());
        $this->assertTrue($node->isDependency());
        $this->assertFalse($node->highlighted());
        $this->assertNull($node->highlight());
        $this->assertTrue($node->highlighted());

        $this->assertFalse($relation->highlighted());
        $this->assertNull($relation->highlight());
        $this->assertTrue($relation->highlighted());
    }

    public function testHighlightingPathInARecursiveGraphDoesNotSegfault()
    {
        $node = Node::of(new class {
        });
        $node->relate(Relation::of(
            Property::of('self'),
            $node,
        ));

        $this->assertNull($node->highlightPathTo(new \stdClass));
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

    public function testFlagAsDependentOn()
    {
        $object = new class {
            public $foo;
            public $bar;
        };
        $dependency = new class {
        };
        $dependency2 = new class {
        };
        $object->foo = $dependency;
        $object->bar = $dependency2;

        $node = Node::of($object);
        $node->relate($foo = Relation::of(
            Property::of('foo'),
            Node::of($dependency),
        ));
        $node->relate($bar = Relation::of(
            Property::of('bar'),
            Node::of($dependency2),
        ));

        $this->assertFalse($node->isDependent());
        $this->assertNull($node->flagAsDependent());
        $this->assertTrue($node->isDependent());
    }
}
