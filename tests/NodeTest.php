<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Node,
    Relation,
    Relation\Property,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\SetInterface;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testInterface()
    {
        $node = new Node($object = new class {});

        $this->assertInstanceOf(SetInterface::class, $node->relations());
        $this->assertSame(Relation::class, (string) $node->relations()->type());
        $this->assertInstanceOf(UrlInterface::class, $node->location());
        $this->assertSame('file://'.__FILE__, (string) $node->location());
        $this->assertCount(0, $node->relations());
        $this->assertSame($node, $node->relate($relation = new Relation(
            new Property('bar'),
            new Node(new \stdClass)
        )));
        $this->assertCount(1, $node->relations());
        $this->assertSame([$relation], $node->relations()->toPrimitive());
        $this->assertNull($node->removeRelations());
        $this->assertCount(0, $node->relations());
        $this->assertTrue($node->comesFrom($object));
        $this->assertFalse($node->comesFrom(new class {}));
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
        $node = new Node(new class {});
        $node->relate(new Relation(
            new Property('self'),
            $node
        ));

        $this->assertNull($node->highlightPathTo(new \stdClass));
    }

    public function testDependsOn()
    {
        $object = new class {
            public $foo;
        };
        $dependency = new class {};
        $object->foo = $dependency;

        $node = new Node($object);
        $node->relate(new Relation(
            new Property('foo'),
            new Node($dependency)
        ));

        $this->assertTrue($node->dependsOn($dependency));
        $this->assertFalse($node->dependsOn(new class {}));
    }

    public function testFlagAsDependentOn()
    {
        $object = new class {
            public $foo;
            public $bar;
        };
        $dependency = new class {};
        $dependency2 = new class {};
        $object->foo = $dependency;
        $object->bar = $dependency2;

        $node = new Node($object);
        $node->relate($foo = new Relation(
            new Property('foo'),
            new Node($dependency)
        ));
        $node->relate($bar = new Relation(
            new Property('bar'),
            new Node($dependency2)
        ));

        $this->assertFalse($node->isDependent());
        $this->assertNull($node->flagAsDependent());
        $this->assertTrue($node->isDependent());
    }
}
