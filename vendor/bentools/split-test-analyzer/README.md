[![Latest Stable Version](https://poser.pugx.org/bentools/split-test-analyzer/v/stable)](https://packagist.org/packages/bentools/split-test-analyzer)
[![License](https://poser.pugx.org/bentools/split-test-analyzer/license)](https://packagist.org/packages/bentools/split-test-analyzer)
[![Build Status](https://img.shields.io/travis/bpolaszek/split-test-analyzer/master.svg?style=flat-square)](https://travis-ci.org/bpolaszek/split-test-analyzer)
[![Coverage Status](https://coveralls.io/repos/github/bpolaszek/picker/badge.svg?branch=master)](https://coveralls.io/github/bpolaszek/picker?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/split-test-analyzer.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/split-test-analyzer)
[![Total Downloads](https://poser.pugx.org/bentools/split-test-analyzer/downloads)](https://packagist.org/packages/bentools/split-test-analyzer)

# Split Test Analyzer

This PHP tool will help you compute, for different versions of a split test, their probability to be the best one.

It is a PHP port of the JS tool used on [AB split test graphical Bayesian calculator](https://www.peakconversion.com/2012/02/ab-split-test-graphical-calculator/).

Concept
-------

A `Variation` object is composed of 3 informations:

* A `key` to identify it (the Landing page / banner id for instance)
* The total number of actions (let's say, the unique visitors)
* The number of successful actions (let's say, the conversions)

Now you can compare those 3 objects and compute their probability to be the best.

Usage
-----

```php
use BenTools\SplitTestAnalyzer\SplitTestAnalyzer;
use BenTools\SplitTestAnalyzer\Variation;

require_once __DIR__ . '/vendor/autoload.php';

$variations = [
    new Variation('LP #1', 8686347, 932),
    new Variation('LP #2', 7305026, 804),
    new Variation('LP #3', 4592639, 371),
    new Variation('LP #4', 4590186, 402),
    new Variation('LP #5', 4532325, 470),
    new Variation('LP #6', 4531653, 494),
];

$predictor = SplitTestAnalyzer::create()->withVariations(...$variations);
foreach ($predictor->getResult() as $key => $value) {
    printf('%s has %d%% chances to be the best one.' . PHP_EOL, $key, $value);
}

print PHP_EOL;

printf('The best one is: %s', $predictor->getBestVariation());
```

Output:
```bash
LP #1 has 15% chances to be the best one.
LP #2 has 43% chances to be the best one.
LP #3 has 0% chances to be the best one.
LP #4 has 0% chances to be the best one.
LP #5 has 7% chances to be the best one.
LP #6 has 34% chances to be the best one.

The best one is: LP #2
```

Because of the randomness used in Bayes calculator, results may slightly vary.

To improve performance you can lower the amount of samples with `SplitTestAnalyzer::create(100)`, but then you'll have to choose between performance and reliability.


Installation
------------

This library requires PHP 7.0+.

> composer require bentools/split-test-analyzer

Tests
-----

> ./vendor/bin/phpunit


See also
--------

[bentools/cartesian-product](https://github.com/bpolaszek/cartesian-product)

[bentools/pager](https://github.com/bpolaszek/bentools-pager)