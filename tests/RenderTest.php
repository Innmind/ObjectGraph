<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    Render,
    Lookup,
    Locate,
};
use Innmind\Filesystem\File\Content;
use Fixtures\Innmind\ObjectGraph\Foo;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class RenderTest extends TestCase
{
    public function testInvokation()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $graph = Lookup::of()($root);
        $graph = Locate::of()($graph);
        $graph = $graph->mapNode(static fn($node) => match ($node->comesFrom($leaf)) {
            true => $node->flagAsDependency(),
            false => $node,
        });
        $dot = Render::of()($graph);

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotSame('', $dot->toString());
        $lines = $dot
            ->lines()
            ->map(static fn($line) => $line->str()->trim()->toString())
            ->toList();
        $this->assertCount(11, $lines);
        $this->assertSame('digraph G {', $lines[0]);
        $this->assertSame('rankdir="LR";', $lines[1]);
        $this->assertStringStartsWith('object_', $lines[2]);
        $this->assertStringEndsWith('[label="a"];', $lines[2]);
        $this->assertStringStartsWith('object_', $lines[3]);
        $this->assertStringEndsWith('[label="b"];', $lines[3]);
        $this->assertStringStartsWith('object_', $lines[4]);
        $this->assertStringEndsWith('[label="a"];', $lines[4]);
        $this->assertStringStartsWith('object_', $lines[5]);
        $this->assertStringEndsWith('[label="a"];', $lines[5]);
        $this->assertStringStartsWith('object_', $lines[6]);
        $this->assertStringContainsString('[shape="hexagon", style="filled", fillcolor="#00ff00", label="Fixtures\\\\Innmind\\\\ObjectGraph\\\\Foo", URL="file://', $lines[6]);
        $this->assertStringEndsWith('fixtures/Foo.php"];', $lines[6]);
        $this->assertStringStartsWith('object_', $lines[7]);
        $this->assertStringContainsString('[label="Fixtures\\\\Innmind\\\\ObjectGraph\\\\Foo", URL="file://', $lines[7]);
        $this->assertStringEndsWith('fixtures/Foo.php"];', $lines[7]);
        $this->assertStringStartsWith('object_', $lines[8]);
        $this->assertStringContainsString('[shape="box", style="filled", fillcolor="#ffb600", label="Fixtures\\\\Innmind\\\\ObjectGraph\\\\Foo", URL="file://', $lines[8]);
        $this->assertStringEndsWith('fixtures/Foo.php"];', $lines[8]);
        $this->assertStringStartsWith('object_', $lines[9]);
        $this->assertStringContainsString('[label="Fixtures\\\\Innmind\\\\ObjectGraph\\\\Foo", URL="file://', $lines[9]);
        $this->assertStringEndsWith('fixtures/Foo.php"];', $lines[9]);
        $this->assertSame('}', $lines[10]);
    }

    public function testRenderRecursiveGraph()
    {
        // a <-----|
        //  |-> b -|
        $a = new class {
            public $foo;
        };
        $b = new class {
            public $bar;
        };
        $a->foo = $b;
        $b->bar = $a;

        $dot = Render::of()(Lookup::of()($a));

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotSame('', $dot->toString());
        $lines = $dot
            ->lines()
            ->map(static fn($line) => $line->str()->trim()->toString())
            ->toList();
        $this->assertCount(7, $lines);
    }

    public function testRenderFromTopToBottom()
    {
        // root
        //  |-> a
        //  |    |-> leaf <-|
        //  |-> b ----------|
        $leaf = new Foo;
        $a = new Foo($leaf);
        $b = new Foo($leaf);
        $root = new Foo($a, $b);

        $graph = Lookup::of()($root);
        $graph = Locate::of()($graph);
        $graph = $graph->mapNode(static fn($node) => match ($node->comesFrom($leaf)) {
            true => $node->flagAsDependency(),
            false => $node,
        });
        $dot = Render::of()->fromTopToBottom()($graph);

        $this->assertInstanceOf(Content::class, $dot);
        $this->assertNotSame('', $dot->toString());
        $lines = $dot
            ->lines()
            ->map(static fn($line) => $line->str()->trim()->toString())
            ->toList();
        $this->assertCount(11, $lines);
        $this->assertSame('rankdir="TB";', $lines[1]);
    }
}
