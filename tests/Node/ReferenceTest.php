<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\Node;

use Innmind\ObjectGraph\Node\Reference;
use PHPUnit\Framework\TestCase;

class ReferenceTest extends TestCase
{
    public function testInterface()
    {
        $reference = new Reference($object = new \stdClass);

        $this->assertSame(\spl_object_hash($object), (string) $reference);
    }

    public function testEquals()
    {
        $reference = new Reference($object = new \stdClass);

        $this->assertTrue($reference->equals(new Reference($object)));
        $this->assertFalse($reference->equals(new Reference(new \stdClass)));
    }
}
