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
        $root = Node::of(new class {
        });
        $level1 = Node::of(new class {
        });
        $level2 = Node::of($dependency = new class {
        });
        $level3 = Node::of(new class {
        });
        $root->relate(Relation::of(
            Property::of('level1'),
            $level1,
        ));
        $level1->relate(Relation::of(
            Property::of('level2'),
            $level2,
        ));
        $level2->relate(Relation::of(
            Property::of('level3'),
            $level3,
        ));
        $level3->relate(Relation::of(
            Property::of('recursion'),
            $level1,
        ));
        $flagDependencies = new FlagDependencies($dependency);

        $this->assertNull($flagDependencies($root));
        $this->assertTrue($level2->isDependency());
    }
}
