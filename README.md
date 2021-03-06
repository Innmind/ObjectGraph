# ObjectGraph

[![Build Status](https://github.com/innmind/objectgraph/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/objectgraph/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/objectgraph/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/objectgraph)
[![Type Coverage](https://shepherd.dev/github/innmind/objectgraph/coverage.svg)](https://shepherd.dev/github/innmind/objectgraph)

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
    LocationRewriter\SublimeHandler,
    Clusterize\ByNamespace,
};
use Innmind\Server\Control\{
    ServerFactory,
    Server\Command,
};

$graph = new Graph;
$visualize = new Visualize(
    new SublimeHandler, // optional, useful to open the file in Sublime Text instead of the browser
    new ByNamespace( // optional
        Map::of(NamespacePattern::class, 'string')
            (new NamespacePattern('MyProject\App'), 'app')
            (new NamespacePattern('MyProject\Domain'), 'domain')
            (new NamespacePattern('VendorA'), 'infrastructure')
            (new NamespacePattern('VendorB'), 'infrastructure'),
    ),
);

$objectGraph = $graph($theRootObjectOfYourApp); // the object could be the framework instance for example

ServerFactory::build()
    ->processes()
    ->execute(
        Command::foreground('dot')
            ->withShortOption('Tsvg')
            ->withShortOption('o', 'graph.svg')
            ->withInput($visualize($objectGraph)),
    )
    ->wait();
```

This will generate a `graph.svg` file representing the object graph of your application.

**Note**: This example uses `innmind/server-control` to generate the svg file but the package is not a direct dependency, you can use the stream returned by `$visualize()` however you wish.

**Note 2**: The use of `innmind/server-control` in this example won't work on Windows (at time of writing) as the library doesn't support the platform. To accomplish the same result you could write `file_put_contents('graph.dot', (string) $visualize($objectGraph))` and then run manually the `dot` command in you terminal.

**Note 3**: You can pass an implementation of [`LocationRewriter`](src/LocationRewriter.php) as the first argument of `Visualize` so you can rewrite the url to the class file that will be used in the generated graph (useful if you want to generate urls to [open the files directly in your IDE](https://github.com/sanduhrs/phpstorm-url-handler#usage)).

**Note 4**: The `Graph` does a little [trick](src/Graph.php#L73) to discover the objects in iterables so the whole graph can be discovered, it consists in a modified representation of iterables in the graph. If the iterable is an object its class won't be displayed in the graph, instead it will be displayed as an `ArrayObject` containing a set of pairs.

**Note 5**: You can flag dependencies (orm, http transport, etc...) and remove their sub-graph via [`src/Visitor/FlagDependencies.php`](src/Visitor/FlagDependencies.php) and [`src/Visitor/RemoveDependenciesSubGraph.php`](src/Visitor/RemoveDependenciesSubGraph.php).

**Note 6**: You can flag the objects depending on a set of dependencies (such as controllers on a command bus) via [`src/Visitor/FlagDependents.php`](src/Visitor/FlagDependents.php).

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
    (new Graph)($foo),
); // true

$bar->someProperty = $foo;

(new Acyclic)(
    (new Graph)($foo),
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
            new Router($controllers),
        ),
    ),
);
$stack = Stack::of(
    CatchExceptions::class,
    Security::class,
    Router::class,
);

$stack((new Graph)($requestHandler)); // true

$requestHandler = new Security(
    new CatchExceptions(
        new Debug(
            new Router($controllers),
        ),
    ),
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
    'BoundedContext\\Baz',
);

$object = new BoundedContext\Foo\SomeClass(
    new Indirection(
        new BoundedContext\Bar\SomeClass,
    ),
);

$boundary((new Graph)($object)); // false as Foo depends on Bar
```
