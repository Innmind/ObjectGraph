<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Relation;

use Innmind\ObjectGraph\Relation\Property;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testInterface()
    {
        $class = Property::of('foo');

        $this->assertSame('foo', $class->toString());
    }
}
