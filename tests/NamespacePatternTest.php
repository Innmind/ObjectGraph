<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph;

use Innmind\ObjectGraph\{
    NamespacePattern,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class NamespacePatternTest extends TestCase
{
    public function testInterface()
    {
        $namespace = new NamespacePattern('Foo\Bar');

        $this->assertSame('Foo\\Bar', (string) $namespace);
    }

    public function testThrowWhenNamespaceStartsWithANumber()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Foo\\42Bar');

        new NamespacePattern('Foo\\42Bar');
    }

    public function testThrowWhenEmptyNamespace()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('');

        new NamespacePattern('');
    }
}
