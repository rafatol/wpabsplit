<?php

namespace BenTools\SplitTestAnalyzer\Tests;

use BenTools\SplitTestAnalyzer\Variation;
use PHPUnit\Framework\TestCase;

class VariationTest extends TestCase
{

    public function testValidObject()
    {
        $version = new Variation('foo', 20, 5);
        $this->assertEquals('foo', $version->getKey());
        $this->assertEquals('foo', (string) $version);
        $this->assertEquals(5, $version->getNbSuccesses());
        $this->assertEquals(15, $version->getNbFailures());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidObject()
    {
        new Variation('foo', 20, 21);
    }
}
