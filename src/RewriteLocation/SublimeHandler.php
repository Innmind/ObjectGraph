<?php
declare(strict_types = 1);

namespace Innmind\ObjectGraph\RewriteLocation;

use Innmind\ObjectGraph\RewriteLocation;
use Innmind\Url\{
    Url,
    Scheme,
};

/**
 * Replace url scheme to open the files in Sublime Text
 *
 * @see https://gist.github.com/Baptouuuu/7d6211904e97faf18c6c2c024069c7f1
 * @see https://yourmacguy.wordpress.com/2013/07/17/make-your-own-url-handler/
 *
 * @psalm-immutable
 */
final class SublimeHandler implements RewriteLocation
{
    #[\Override]
    public function __invoke(Url $location): Url
    {
        return $location->withScheme(Scheme::of('sublime'));
    }
}
