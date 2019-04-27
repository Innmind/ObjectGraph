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
}
