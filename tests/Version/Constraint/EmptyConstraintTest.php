<?php

namespace Version\Constraint;

class EmptyConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $c = new EmptyConstraint();
        $this->assertTrue($c->match('1.2.5'));
    }
}
