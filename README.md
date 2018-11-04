# ObjectGraph

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Http/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Http/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/ObjectGraph/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Http/build-status/develop) |

Samll library to generate an abtract graph out of an object and its dependencies.

You can then visualize this graph by rendering it with graphviz, or you could also run some analysis on it to check boundaries in your app are not crossed (enforce bounded context).

## Installation

```sh
composer require innmind/object-graph
```

## Usage

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
