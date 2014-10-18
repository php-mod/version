<?php

namespace Version\Constraint;

class VersionConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $c = new VersionConstraint('=', '1.0');
        $this->assertTrue($c->match('1.0'));
        $this->assertTrue($c->match('1.0.0'));
        $this->assertFalse($c->match('2.0'));

        $c = new VersionConstraint('>', '1.0');
        $this->assertFalse($c->match('1.0'));
        $this->assertFalse($c->match('1.0.0'));
        $this->assertTrue($c->match('1.0.1'));
        $this->assertTrue($c->match('2.0'));

        $c = new VersionConstraint('<', '1.0');
        $this->assertFalse($c->match('1.0'));
        $this->assertFalse($c->match('1.0.0'));
        $this->assertFalse($c->match('2.0.0'));
        $this->assertTrue($c->match('0.2'));
        $this->assertTrue($c->match('0.4.5'));

    }
}
