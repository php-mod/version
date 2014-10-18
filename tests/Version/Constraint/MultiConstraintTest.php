<?php

namespace Version\Constraint;

class MultiConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $c1 = new VersionConstraint('=', '1.5.4');
        $c2 = new VersionConstraint('>', '1.7.2');
        $c3 = new VersionConstraint('<=', '2.0.1');
        $c4 = new MultiConstraint(array($c2, $c3));
        $c = new MultiConstraint(array($c1, $c4), false);

        $this->assertFalse($c->match('0.2'));
        $this->assertFalse($c->match('0.4.5'));
        $this->assertFalse($c->match('1.0'));
        $this->assertFalse($c->match('1.0.0'));
        $this->assertFalse($c->match('1.0.1'));
        $this->assertTrue($c->match('1.5.4'));
        $this->assertFalse($c->match('1.6'));
        $this->assertTrue($c->match('1.7.3'));
        $this->assertTrue($c->match('2.0.0'));
        $this->assertFalse($c->match('2.2'));

    }
}
