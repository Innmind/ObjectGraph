<?php
declare(strict_types = 1);

namespace Tests\Innmind\ObjectGraph\LocationRewriter;

use Innmind\ObjectGraph\{
    LocationRewriter\SublimeHandler,
    LocationRewriter,
};
use Innmind\Url\Url;
use PHPUnit\Framework\TestCase;

class SublimeHandlerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(LocationRewriter::class, new SublimeHandler);
    }

    public function testInvokation()
    {
        $url = (new SublimeHandler)(Url::of('http://example.com/foo'));

        $this->assertInstanceOf(Url::class, $url);
        $this->assertSame('sublime://example.com/foo', $url->toString());
    }
}
