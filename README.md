# ObjectGraph

[![Build Status](https://github.com/innmind/objectgraph/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/objectgraph/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/objectgraph/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/objectgraph)
[![Type Coverage](https://shepherd.dev/github/innmind/objectgraph/coverage.svg)](https://shepherd.dev/github/innmind/objectgraph)

Small library to generate an abstract graph out of an object and its dependencies.

You can then visualize this graph by rendering it with graphviz.

## Installation

```sh
composer require innmind/object-graph
```

## Usage

```php
use Innmind\ObjectGraph\{
    Lookup,
    Render,
    RewriteLocation\SublimeHandler,
};
use Innmind\OperatingSystem\Factory;
use Innmind\Server\Control\Server\Command;

$lookup = Lookup::of();
$render = Render::of(
    new SublimeHandler, // optional, useful to open the file in Sublime Text instead of the browser
);

$objectGraph = $lookup($theRootObjectOfYourApp); // the object could be the framework instance for example

Factory::build()
    ->control()
    ->processes()
    ->execute(
        Command::foreground('dot')
            ->withShortOption('Tsvg')
            ->withShortOption('o', 'graph.svg')
            ->withInput($render($objectGraph)),
    )
    ->unwrap()
    ->wait();
```

This will generate a `graph.svg` file representing the object graph of your application.

> [!NOTE]
> This example uses `innmind/operating-system` to generate the svg file but the package is not a direct dependency, you can use the content returned by `$render()` however you wish.

> [!NOTE]
> You can pass an implementation of [`RewriteLocation`](src/RewriteLocation.php) as the first argument of `Render` so you can rewrite the url to the class file that will be used in the generated graph (useful if you want to generate urls to [open the files directly in your IDE](https://github.com/sanduhrs/phpstorm-url-handler#usage)).
