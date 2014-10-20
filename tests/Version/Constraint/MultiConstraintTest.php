<?php

namespace Version\Constraint;

use Version\Constraint;
use Version\Operator;
use Version\Version;

class MultiConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $c1 = new SimpleConstraint(new Operator('='), Version::parse('1.5.4'));
        $c2 = new SimpleConstraint(new Operator('>'), Version::parse('1.7.2'));
        $c3 = new SimpleConstraint(new Operator('<='), Version::parse('2.0.1'));
        $c4 = new MultiConstraint(array($c2, $c3));
        $c = new MultiConstraint(array($c1, $c4), false);

        $this->assertFalse($c->matches(Constraint::parse('0.2')));
        $this->assertFalse($c->matches(Constraint::parse('0.4.5')));
        $this->assertFalse($c->matches(Constraint::parse('1.0')));
        $this->assertFalse($c->matches(Constraint::parse('1.0.0')));
        $this->assertFalse($c->matches(Constraint::parse('1.0.1')));
        $this->assertFalse($c->matches(Constraint::parse('1.6')));
        $this->assertTrue($c->matches(Constraint::parse('1.7.3')));
        $this->assertTrue($c->matches(Constraint::parse('2.0.0')));
        $this->assertFalse($c->matches(Constraint::parse('2.2')));
        //$this->assertTrue($c->matches(Constraint::parse('1.5.4')));
    }
}
