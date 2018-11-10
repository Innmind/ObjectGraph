<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\ObjectGraph;

final class Indirection
{
    private $a;
    private $b;

    public function __construct($a = null, $b = null)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
