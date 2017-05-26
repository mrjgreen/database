<?php


class ExpressionTest extends \PHPUnit\Framework\TestCase
{
    public function testItCorrectlyImplementsToString()
    {
        $expression = new \Database\Query\Expression('test expression = 1');

        $this->assertEquals('test expression = 1', (string)$expression);
    }
}
