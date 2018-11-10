# ObjectGraph

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Http/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Http/build-status/develop) |

Small library to generate an abstract graph out of an object and its dependencies.

You can then visualize this graph by rendering it with graphviz, or you could also run some analysis on it to check boundaries in your app are not crossed (enforce bounded context).

## Installation

```sh
composer require innmind/object-graph
```

## Usage

### Visualization

```php
use Innmind\ObjectGraph\{
    Graph,
    Visualize,
};

$graph = new Graph;
$visualize = new Visualize;

$objectGraph = $graph($theRootObjectOfYourApp); // the object could be the framework instance for example

$os
    ->control()
    ->processes()
    ->execute(
        Command::foreground('dot')
            ->withShortOption('Tsvg')
            ->withShortOption('o', 'graph.svg')
            ->withInput($visualize($objectGraph))
    )
    ->wait();
```

This will generate a `graph.svg` file representing the object graph of your application.

### Assertions

#### Acylic

```php
use Innmind\ObjectGraph\{
    Assert\Acyclic,
    Graph,
};

$foo = new Foo;
$bar = new Bar;
$foo->someProperty = $bar;

(new Acyclic)(
    (new Graph)($foo)
); // true

$bar->someProperty = $foo;

(new Acyclic)(
    (new Graph)($foo)
); // false
```

#### Stack

Assert that somme class depends on another, useful to make sure decorators are assembled correctly.

```php
use Innmind\ObjectGraph\{
    Assert\Stack,
    Graph,
};

$requestHandler = new CatchExceptions(
    new Debug(
        new Security(
            new Router($controllers)
        )
    )
);
$stack = Stack::of(
    CatchExceptions::class,
    Security::class,
    Router::class
);

$stack((new Graph)($requestHandler)); // true

$requestHandler = new Security(
    new CatchExceptions(
        new Debug(
            new Router($controllers)
        )
    )
);

$stack((new Graph)($requestHandler)); // false as Security is above CatchExceptions
```

#### Boundaries

Useful to make sure some bounded context doesnt depend on another.

```php
use Innmind\ObjectGraph\{
    Assert\Boundary,
    Graph,
};

$boundary = Boundary::of(
    'BoundedContext\\Foo', // namespace to protect
    'BoundedContext\\Bar',
    'BoundedContext\\Baz'
);

$object = new BoundedContext\Foo\SomeClass(
    new Indirection(
        new BoundedContext\Bar\SomeClass
    )
);

$boundary((new Graph)($object)); // false as Foo depends on Bar
```
