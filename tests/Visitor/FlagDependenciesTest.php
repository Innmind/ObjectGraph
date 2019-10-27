<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Visitor;

use Innmind\ObjectGraph\{
    Visitor\FlagDependencies,
    Node,
    Relation,
    Relation\Property,
};
use PHPUnit\Framework\TestCase;

class FlagDependenciesTest extends TestCase
{
    public function testInvokation()
    {
        // root
        //  |-> level1 <----------|
        //       |-> level2       |
        //            |-> level3 -|
        $root = new Node(new class {});
        $level1 = new Node(new class {});
        $level2 = new Node($dependency = new class {});
        $level3 = new Node(new class {});
        $root->relate(new Relation(
            new Property('level1'),
            $level1
        ));
        $level1->relate(new Relation(
            new Property('level2'),
            $level2
        ));
        $level2->relate(new Relation(
            new Property('level3'),
            $level3
        ));
        $level3->relate(new Relation(
            new Property('recursion'),
            $level1
        ));
        $flagDependencies = new FlagDependencies($dependency);

        $this->assertNull($flagDependencies($root));
        $this->assertTrue($level2->isDependency());
    }
}
