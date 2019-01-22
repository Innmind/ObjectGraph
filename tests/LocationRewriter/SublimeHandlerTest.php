<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\LocationRewriter;

use Innmind\ObjectGraph\{
    LocationRewriter\SublimeHandler,
    LocationRewriter,
};
use Innmind\Url\{
    UrlInterface,
    Url,
};
use PHPUnit\Framework\TestCase;

class SublimeHandlerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(LocationRewriter::class, new SublimeHandler);
    }

    public function testInvokation()
    {
        $url = (new SublimeHandler)(Url::fromString('http://example.com/foo'));

        $this->assertInstanceOf(UrlInterface::class, $url);
        $this->assertSame('sublime://example.com/foo', (string) $url);
    }
}
