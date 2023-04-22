<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\Node\Reference;
use PHPUnit\Framework\TestCase;

class ReferenceTest extends TestCase
{
    public function testInterface()
    {
        $reference = Reference::of($object = new \stdClass);

        $this->assertSame(\spl_object_hash($object), $reference->toString());
    }

    public function testEquals()
    {
        $reference = Reference::of($object = new \stdClass);

        $this->assertTrue($reference->equals(Reference::of($object)));
        $this->assertFalse($reference->equals(Reference::of(new \stdClass)));
    }
}
