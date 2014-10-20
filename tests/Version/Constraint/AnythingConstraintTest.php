<?php

namespace Version\Constraint;

use Version\Constraint;

class AnythingConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $c = new AnythingConstraint();
        $this->assertTrue($c->matches(Constraint::parse('1.2.5')));
    }
}
