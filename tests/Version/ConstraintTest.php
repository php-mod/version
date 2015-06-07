<?php

namespace Version;

use UnexpectedValueException;
use Version\Constraint\AnythingConstraint;
use Version\Constraint\MultiConstraint;
use Version\Constraint\SimpleConstraint;

class ConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider simpleConstraints
     * @param $input
     * @param $expected
     * @param string $message
     */
    public function testParseConstraintsSimple($input, $expected, $message = '')
    {
        $this->assertSame(
            (string) $expected,
            (string) Constraint::parse($input),
            $message . ' INPUT: ' . $input
        );
    }

    public function simpleConstraints()
    {
        return array(
            array(
                '<1.2.3.4-stable',
                new SimpleConstraint(new Operator('<'), Version::parse('1.2.3.4')),
                'lesser than override'
            ),
            'match any'            =>
                array(
                    '*',
                    new AnythingConstraint()
                ),
            'match any/2'          =>
                array(
                    '*.*',
                    new AnythingConstraint()
                ),
            'match any/3'          =>
                array(
                    '*.x.*',
                    new AnythingConstraint()
                ),
            'match any/4'          => array('x.x.x.*',
                new AnythingConstraint()),
            'not equal'            => array('<>1.0.0',
                new SimpleConstraint(new Operator('<>'),
                    Version::parse('1.0.0.0'))),
            'not equal/2'          => array('!=1.0.0',
                new SimpleConstraint(new Operator('!='),
                    Version::parse('1.0.0.0'))),
            'greater than'         => array('>1.0.0',
                new SimpleConstraint(new Operator('>'),
                    Version::parse('1.0.0.0'))),
            'lesser than'          =>
                array(
                    '<1.2.3.4',
                    new SimpleConstraint(new Operator('<'), Version::parse('1.2.3.4'))
                ),
            'less/eq than'         => array('<=1.2.3',
                new SimpleConstraint(new Operator('<='),
                    Version::parse('1.2.3.0'))),
            'great/eq than'        => array('>=1.2.3',
                new SimpleConstraint(new Operator('>='),
                    Version::parse('1.2.3.0'))),
            'equals'               => array('=1.2.3',
                new SimpleConstraint(new Operator('='),
                    Version::parse('1.2.3.0'))),
            'double equals'        => array('==1.2.3',
                new SimpleConstraint(new Operator('='),
                    Version::parse('1.2.3.0'))),
            'no op means eq'       => array('1.2.3',
                new SimpleConstraint(new Operator('='),
                    Version::parse('1.2.3.0'))),
            'completes version'    => array('=1.0',
                new SimpleConstraint(new Operator('='),
                    Version::parse('1.0.0.0'))),
            'shorthand beta'       => array('1.2.3b5',
                new SimpleConstraint(new Operator('='),
                    Version::parse('1.2.3.0-beta5'))),
            'accepts spaces'       => array('>= 1.2.3',
                new SimpleConstraint(new Operator('>='),
                    Version::parse('1.2.3.0'))),
        );
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Invalid operator "~>", you probably meant to use the "~" operator
     */
    public function testParseConstraintsNudgesRubyDevsTowardsThePathOfRighteousness()
    {
        Constraint::parse('~>1.2');
    }

    /**
     * @dataProvider wildcardConstraints
     * @param $input
     * @param $min
     * @param $max
     */
    public function testParseConstraintsWildcard($input, $min, $max)
    {
        if ($min) {
            $expected = new MultiConstraint(array($min, $max));
        } else {
            $expected = $max;
        }

        $this->assertSame((string) $expected, (string) Constraint::parse($input));
    }

    public function wildcardConstraints()
    {
        return array(
            array('2.*',     new SimpleConstraint(new Operator('>='), Version::parse('2.0.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('3.0.0.0'))),
            array('2.0.*',   new SimpleConstraint(new Operator('>='), Version::parse('2.0.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.1.0.0'))),
            array('2.2.x',   new SimpleConstraint(new Operator('>='), Version::parse('2.2.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.3.0.0'))),
            array('2.1.3.*', new SimpleConstraint(new Operator('>='), Version::parse('2.1.3.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.1.4.0'))),
            array('20.*',    new SimpleConstraint(new Operator('>='), Version::parse('20.0.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('21.0.0.0'))),
            array('2.10.x',  new SimpleConstraint(new Operator('>='), Version::parse('2.10.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.11.0.0'))),
            array('0.*',     null, new SimpleConstraint(new Operator('<'), Version::parse('1.0.0.0'))),
        );
    }

    /**
     * @dataProvider tildeConstraints
     * @param $input
     * @param $min
     * @param $max
     */
    public function testParseTildeWildcard($input, $min, $max)
    {
        if ($min) {
            $expected = new MultiConstraint(array($min, $max));
        } else {
            $expected = $max;
        }

        $this->assertSame(
            (string) $expected,
            (string) Constraint::parse($input),
            'INPUT: ' . $input
        );
    }

    public function tildeConstraints()
    {
        return array(
            array('~1',       new SimpleConstraint(new Operator('>='), Version::parse('1.0.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.0.0.0'))),
            array('~1.0',     new SimpleConstraint(new Operator('>='), Version::parse('1.0.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.0.0.0'))),
            array('~1.0.0',     new SimpleConstraint(new Operator('>='), Version::parse('1.0.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('1.1.0.0'))),
            array('~1.2',     new SimpleConstraint(new Operator('>='), Version::parse('1.2.0.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.0.0.0'))),
            array('~1.2.3',   new SimpleConstraint(new Operator('>='), Version::parse('1.2.3.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('1.3.0.0'))),
            array('~1.2.3.4', new SimpleConstraint(new Operator('>='), Version::parse('1.2.3.4')),
                new SimpleConstraint(new Operator('<'), Version::parse('1.2.4.0'))),
            array('~1.2-beta',new SimpleConstraint(new Operator('>='), Version::parse('1.2.0.0-beta')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.0.0.0'))),
            array('~1.2-b2',  new SimpleConstraint(new Operator('>='), Version::parse('1.2.0.0-beta2')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.0.0.0'))),
            array('~1.2-BETA2', new SimpleConstraint(new Operator('>='), Version::parse('1.2.0.0-beta2')),
                new SimpleConstraint(new Operator('<'), Version::parse('2.0.0.0'))),
            array('~1.2.2', new SimpleConstraint(new Operator('>='), Version::parse('1.2.2.0')),
                new SimpleConstraint(new Operator('<'), Version::parse('1.3.0.0'))),
            array('~1.2.2-stable', new SimpleConstraint(new Operator('>='), Version::parse('1.2.2.0-stable')),
                new SimpleConstraint(new Operator('<'), Version::parse('1.3.0.0'))),
        );
    }

    public function testParseConstraintsMulti()
    {
        $first = new SimpleConstraint(new Operator('>'), Version::parse('2.0.0.0'));
        $second = new SimpleConstraint(new Operator('<='), Version::parse('3.0.0.0'));
        $multi = new MultiConstraint(array($first, $second));
        $this->assertSame((string) $multi, (string) Constraint::parse('>2.0,<=3.0'));
    }

    public function testParseConstraintsMultiDisjunctiveHasPrioOverConjuctive()
    {
        $first = new SimpleConstraint(new Operator('>'), Version::parse('2.0.0.0'));
        $second = new SimpleConstraint(new Operator('<'), Version::parse('2.0.5.0'));
        $third = new SimpleConstraint(new Operator('>'), Version::parse('2.0.6.0'));
        $multi1 = new MultiConstraint(array($first, $second));
        $multi2 = new MultiConstraint(array($multi1, $third), false);
        $this->assertSame((string) $multi2, (string) Constraint::parse('>2.0,<2.0.5 | >2.0.6'));
    }

    public function testParseConstraintsMultiWithStabilities()
    {
        $first = new SimpleConstraint(new Operator('>'), Version::parse('2.0.0.0'));
        $second = new SimpleConstraint(new Operator('<='), Version::parse('3.0.0.0'));
        $multi = new MultiConstraint(array($first, $second));
        $this->assertSame((string) $multi, (string) Constraint::parse('>2.0,<=3.0'));
    }

    /**
     * @dataProvider failingConstraints
     * @expectedException UnexpectedValueException
     * @param $input
     */
    public function testParseConstraintsFails($input)
    {
        Constraint::parse($input);
    }

    public function failingConstraints()
    {
        return array(
            'empty '            => array(''),
            'invalid version'   => array('1.0.0-meh'),
        );
    }

    /**
     * @dataProvider isSubsetOfProvider
     * @param $constraint1
     * @param $constraint2
     * @param $expected
     */
    public function testIsSubsetOf($constraint1, $constraint2, $expected)
    {
        $constraint1 = Constraint::parse($constraint1);
        $constraint2 = Constraint::parse($constraint2);

        $this->assertSame(
            $expected,
            $constraint1->isSubsetOf($constraint2),
            (string) $constraint1 . ($expected ? '' : ' does\'nt') .
            ' satisfy ' . (string) $constraint2
        );
    }

    public function isSubsetOfProvider()
    {
        return array(
            array('1.0', '1.0', true),
            array('1.5.8', '1.5.8', true),
            array('1.0.0', '1.0', true),
            array('1.5.0', '2.5', false),
            array('2.3.0', '1.5.8', false),

            array('2.3.0', '>1.0', true),
            array('2.3.0', '>2.3', false),
            array('2.3.0', '>2.5', false),
            array('=1.0.0.0', '>1.0.0.0', false),

            array('1.0.0', '>=1.0', true),
            array('2.3.0', '>=1.0', true),
            array('2.3.0', '>=5.2.0', false),

            array('2.3.0', '<3.5.8', true),
            array('2.3.0', '<2.3', false),
            array('2.3.0', '<1.5.8', false),

            array('2.3.0', '<=2.3', true),
            array('2.3.0', '<=2.3.0.0', true),
            array('2.3.0', '<=0.3.0.0', false),

            array('1.5.0', '!=2.5', true),
            array('3.5.0', '!=2.5', true),
            array('2.3.0', '!=2.3', false),

            array('1.5.0', '<>2.5', true),
            array('3.5.0', '<>2.5', true),
            array('2.3.0', '<>2.3', false),

            array('2.5.0', '~2.5', true),
            array('2.5.1', '~2.5', true),
            array('2.7.0', '~2.5', true),
            array('3.0.0', '~2.5', false),

            array('2.5.0', '~2.5.3', false),
            array('2.5.1', '~2.5.3', false),
            array('2.5.3', '~2.5.3', true),
            array('2.5.4', '~2.5.3', true),
            array('2.5.4.5', '~2.5.3', true),
            array('2.7.0', '~2.5.3', false),
            array('3.0.0', '~2.5.3', false),

            array('>2.3.0', '1.5.8', false),

            array('>2.3.0', '>1.5.8', true),
            array('>2.3.0', '>2.3.0', true),
            array('>2.3.0', '>2.3.0.1', false),

            array('>2.3.0', '>=1.5.8', true),
            array('>2.3.0', '>=2.3.0', true),
            array('>2.3.0', '>=2.3.0.1', false),

            array('>2.3.0', '<1.5.8', false),
            array('>2.3.0', '<2.3.0', false),
            array('>2.3.0', '<2.3.0.1', false),

            array('>2.3.0', '<=1.5.8', false),
            array('>2.3.0', '<=2.3.0', false),
            array('>2.3.0', '<=2.3.0.1', false),

            array('>2.3.0', '!=1.5.8', true),
            array('>2.3.0', '!=2.3.0', true),
            array('>2.3.0', '!=2.3.0.1', false),

            array('>2.3.0', '<>1.5.8', true),
            array('>2.3.0', '<>2.3.0', true),
            array('>2.3.0', '<>2.3.0.1', false),

            array('>2.3.0', '~1.5.8', false),
            array('>2.3.0', '~2.3', false),
            array('>2.3.0', '~2.4.', false),

            array('>=2.3.0', '1.5.8', false),

            array('>=2.3.0', '>1.5.8', true),
            array('>=2.3.0', '>2.3', false),
            array('>=2.3.0', '>2.3.0.1', false),

            array('>=2.3.0', '>=1.5.8', true),
            array('>=2.3.0', '>=2.3', true),
            array('>=2.3.0', '>=2.3.0.1', false),

            array('>=2.3.0', '<1.5.8', false),
            array('>=2.3.0', '<2.3', false),
            array('>=2.3.0', '<2.3.0.1', false),

            array('>=2.3.0', '<=1.5.8', false),
            array('>=2.3.0', '<=2.3', false),
            array('>=2.3.0', '<=2.3.0.1', false),

            array('>=2.3.0', '!=1.5.8', true),
            array('>=2.3.0', '!=2.3', false),
            array('>=2.3.0', '!=2.3.0.1', false),

            array('>=2.3.0', '<>1.5.8', true),
            array('>=2.3.0', '<>2.3', false),
            array('>=2.3.0', '<>2.3.0.1', false),

            array('>=2.3.0', '~1.5.8', false),
            array('>=2.3.0', '~2.3', false),
            array('>=2.3.0', '~2.4.', false),

            array('<2.3.0', '1.5.8', false),

            array('<2.3.0', '>1.5.8', false),
            array('<2.3.0', '>2.3.0', false),
            array('<2.3.0', '>2.3.0.1', false),

            array('<2.3.0', '>=1.5.8', false),
            array('<2.3.0', '>=2.3.0', false),
            array('<2.3.0', '>=2.3.0.1', false),

            array('<2.3.0', '<1.5.8', false),
            array('<2.3.0', '<2.3.0', true),
            array('<2.3.0', '<2.3.0.1', true),

            array('<2.3.0', '<=1.5.8', false),
            array('<2.3.0', '<=2.3.0', true),
            array('<2.3.0', '<=2.3.0.1', true),

            array('<2.3.0', '!=1.5.8', false),
            array('<2.3.0', '!=2.3.0', true),
            array('<2.3.0', '!=2.3.0.1', true),

            array('<2.3.0', '<>1.5.8', false),
            array('<2.3.0', '<>2.3.0', true),
            array('<2.3.0', '<>2.3.0.1', true),

            array('<2.3.0', '~1.5.8', false),
            array('<2.3.0', '~2.3', false),
            array('<2.3.0', '~2.4.', false),

            array('<=2.3.0', '1.5.8', false),

            array('<=2.3.0', '>1.5.8', false),
            array('<=2.3.0', '>2.3.0', false),
            array('<=2.3.0', '>2.3.0.1', false),

            array('<=2.3.0', '>=1.5.8', false),
            array('<=2.3.0', '>=2.3.0', false),
            array('<=2.3.0', '>=2.3.0.1', false),

            array('<=2.3.0', '<1.5.8', false),
            array('<=2.3.0', '<2.3.0', false),
            array('<=2.3.0', '<2.3.0.1', true),

            array('<=2.3.0', '<=1.5.8', false),
            array('<=2.3.0', '<=2.3.0', true),
            array('<=2.3.0', '<=2.3.0.1', true),

            array('<=2.3.0', '!=1.5.8', false),
            array('<=2.3.0', '!=2.3.0', false),
            array('<=2.3.0', '!=2.3.0.1', true),

            array('<=2.3.0', '<>1.5.8', false),
            array('<=2.3.0', '<>2.3.0', false),
            array('<=2.3.0', '<>2.3.0.1', true),

            array('<=2.3.0', '~1.5.8', false),
            array('<=2.3.0', '~2.3', false),
            array('<=2.3.0', '~2.4.', false),

            array('!=2.3.0', '1.5.8', false),

            array('<>2.3.0', '>1.5.8', false),
            array('!=2.3.0', '>2.3.0', false),
            array('<>2.3.0', '>2.3.0.1', false),

            array('!=2.3.0', '>=1.5.8', false),
            array('<>2.3.0', '>=2.3.0', false),
            array('!=2.3.0', '>=2.3.0.1', false),

            array('<>2.3.0', '<1.5.8', false),
            array('!=2.3.0', '<2.3.0', false),
            array('<>2.3.0', '<2.3.0.1', false),

            array('!=2.3.0', '<=1.5.8', false),
            array('<>2.3.0', '<=2.3.0', false),
            array('!=2.3.0', '<=2.3.0.1', false),

            array('<>2.3.0', '!=1.5.8', false),
            array('!=2.3.0', '!=2.3.0.0', true),
            array('<>2.3.0', '!=2.3.0.1', false),

            array('!=2.3.0', '<>1.5.8', false),
            array('<>2.3.0', '<>2.3', true),
            array('!=2.3.0', '<>2.3.0.1', false),

            array('!=2.3.0', '~1.5.8', false),
            array('!=2.3.0', '~2.3', false),
            array('!=2.3.0', '~2.4.', false),

            array('~2.3.0', '1.5.8', false),

            array('~2.3.5', '>2.3.0', true),
            array('~2.3.5', '>2.3.5', false),
            array('~2.3.5', '>2.3.6', false),

            array('~2.3.5', '>=2.3.0', true),
            array('~2.3.5', '>=2.3.5', true),
            array('~2.3.5', '>=2.3.6', false),

            array('~2.3.5', '<2.3.5', false),
            array('~2.3.5', '<2.3.6', false),
            array('~2.3.5', '<2.4.0', true),

            array('~2.3.5', '<=2.3.5', false),
            array('~2.3.5', '<=2.3.6', false),
            array('~2.3.5', '<=2.4.0', true),

            array('~2.3.5', '!=2.3.0', true),
            array('~2.3.5', '!=2.3.6', false),
            array('~2.3.5', '<>2.4.0', true),

            array('~2.3.5', '~2.3', true),
            array('~2.3.5', '~2.4', false),
            array('~2.3.5', '~2.3', true),
            array('~2.4.5', '~2.3', true),
            array('~2.3', '~2.3.2', false),
            array('~2.4', '~2.3.4', false),
            array('~2.3.5', '~2.3.5', true),
        );
    }

    /**
     * @dataProvider matchesProvider
     * @param $constraint1
     * @param $constraint2
     * @param $expected
     */
    public function testMatches($constraint1, $constraint2, $expected)
    {
        $constraint1 = Constraint::parse($constraint1);
        $constraint2 = Constraint::parse($constraint2);

        $this->assertSame(
            $expected,
            $constraint1->matches($constraint2),
            (string) $constraint1 . ($expected ? '' : ' does\'nt') .
            ' matches ' . (string) $constraint2
        );
    }

    public function matchesProvider()
    {
        return array(
            array('1.0', '1.0', true),
            array('1.5.8', '1.5.8', true),
            array('1.0.0', '1.0', true),
            array('1.5.0', '2.5', false),
            array('2.3.0', '1.5.8', false),

            array('2.3.0', '>1.0', true),
            array('2.3.0', '>2.3', false),
            array('2.3.0', '>2.5', false),
            array('=1.0.0.0', '>1.0.0.0', false),

            array('1.0.0', '>=1.0', true),
            array('2.3.0', '>=1.0', true),
            array('2.3.0', '>=5.2.0', false),

            array('2.3.0', '<3.5.8', true),
            array('2.3.0', '<2.3', false),
            array('2.3.0', '<1.5.8', false),

            array('2.3.0', '<=2.3', true),
            array('2.3.0', '<=2.3.0.0', true),
            array('2.3.0', '<=0.3.0.0', false),

            array('1.5.0', '!=2.5', true),
            array('3.5.0', '!=2.5', true),
            array('2.3.0', '!=2.3', false),

            array('1.5.0', '<>2.5', true),
            array('3.5.0', '<>2.5', true),
            array('2.3.0', '<>2.3', false),

            array('2.5.0', '~2.5', true),
            array('2.5.1', '~2.5', true),
            array('2.7.0', '~2.5', true),
            array('3.0.0', '~2.5', false),

            array('2.5.0', '~2.5.3', false),
            array('2.5.1', '~2.5.3', false),
            array('2.5.3', '~2.5.3', true),
            array('2.5.4', '~2.5.3', true),
            array('2.5.4.5', '~2.5.3', true),
            array('2.7.0', '~2.5.3', false),
            array('3.0.0', '~2.5.3', false),

            array('>2.3.0', '1.5.8', false),

            array('>2.3.0', '>1.5.8', true),
            array('>2.3.0', '>2.3.0', true),
            array('>2.3.0', '>2.3.0.1', true),

            array('>2.3.0', '>=1.5.8', true),
            array('>2.3.0', '>=2.3.0', true),
            array('>2.3.0', '>=2.3.0.1', true),

            array('>2.3.0', '<1.5.8', false),
            array('>2.3.0', '<2.3.0', false),
            array('>2.3.0', '<2.3.2', true),

            array('>2.3.0', '<=1.5.8', false),
            array('>2.3.0', '<=2.3.0', false),
            array('>2.3.0', '<=2.3.0.1', true),

            array('>2.3.0', '!=1.5.8', true),
            array('>2.3.0', '!=2.3.0', true),
            array('>2.3.0', '!=2.3.0.1', true),

            array('>2.3.0', '<>1.5.8', true),
            array('>2.3.0', '<>2.3.0', true),
            array('>2.3.0', '<>2.3.0.1', true),

            array('>2.3.0', '~1.5.8', false),
            array('>2.3.0', '~2.3', true),
            array('>2.3.0', '~2.4', true),

            array('>=2.3.0', '1.5.8', false),

            array('>=2.3.0', '>1.5.8', true),
            array('>=2.3.0', '>2.3', true),
            array('>=2.3.0', '>2.3.0.1', true),

            array('>=2.3.0', '>=1.5.8', true),
            array('>=2.3.0', '>=2.3', true),
            array('>=2.3.0', '>=2.3.0.1', true),

            array('>=2.3.0', '<1.5.8', false),
            array('>=2.3.0', '<2.3', false),
            array('>=2.3.0', '<2.3.0.1', true),

            array('>=2.3.0', '<=1.5.8', false),
            array('>=2.3.0', '<=2.3', true),
            array('>=2.3.0', '<=2.3.0.1', true),

            array('>=2.3.0', '!=1.5.8', true),
            array('>=2.3.0', '!=2.3', true),
            array('>=2.3.0', '!=2.3.0.1', true),

            array('>=2.3.0', '<>1.5.8', true),
            array('>=2.3.0', '<>2.3', true),
            array('>=2.3.0', '<>2.3.0.1', true),

            array('>=2.3.0', '~1.5.8', false),
            array('>=2.3.0', '~2.3', true),
            array('>=2.3.0', '~2.4.', true),

            array('<2.3.0', '1.5.8', true),

            array('<2.3.0', '>1.5.8', true),
            array('<2.3.0', '>2.3.0', false),
            array('<2.3.0', '>2.3.0.1', false),

            array('<2.3.0', '>=1.5.8', true),
            array('<2.3.0', '>=2.3.0', false),
            array('<2.3.0', '>=2.3.0.1', false),

            array('<2.3.0', '<1.5.8', true),
            array('<2.3.0', '<2.3.0', true),
            array('<2.3.0', '<2.3.0.1', true),

            array('<2.3.0', '<=1.5.8', true),
            array('<2.3.0', '<=2.3.0', true),
            array('<2.3.0', '<=2.3.0.1', true),

            array('<2.3.0', '!=1.5.8', true),
            array('<2.3.0', '!=2.3.0', true),
            array('<2.3.0', '!=2.3.0.1', true),

            array('<2.3.0', '<>1.5.8', true),
            array('<2.3.0', '<>2.3.0', true),
            array('<2.3.0', '<>2.3.0.1', true),

            array('<2.3.0', '~1.5.8', true),
            array('<2.3.0', '~2.3', false),
            array('<2.3.0', '~2.4.', false),

            array('<=2.3.0', '1.5.8', true),

            array('<=2.3.0', '>1.5.8', true),
            array('<=2.3.0', '>2.3.0', false),
            array('<=2.3.0', '>2.3.0.1', false),

            array('<=2.3.0', '>=1.5.8', true),
            array('<=2.3.0', '>=2.3.0', true),
            array('<=2.3.0', '>=2.3.0.1', false),

            array('<=2.3.0', '<1.5.8', true),
            array('<=2.3.0', '<2.3.0', true),
            array('<=2.3.0', '<2.3.0.1', true),

            array('<=2.3.0', '<=1.5.8', true),
            array('<=2.3.0', '<=2.3.0', true),
            array('<=2.3.0', '<=2.3.0.1', true),

            array('<=2.3.0', '!=1.5.8', true),
            array('<=2.3.0', '!=2.3.0', true),
            array('<=2.3.0', '!=2.3.0.1', true),

            array('<=2.3.0', '<>1.5.8', true),
            array('<=2.3.0', '<>2.3.0', true),
            array('<=2.3.0', '<>2.3.0.1', true),

            array('<=2.3.0', '~1.5.8', true),
            array('<=2.3.0', '~2.3', true),
            array('<=2.3.0', '~2.4.', false),

            array('!=2.3.0', '1.5.8', true),

            array('<>2.3.0', '>1.5.8', true),
            array('!=2.3.0', '>2.3.0', true),
            array('<>2.3.0', '>2.3.0.1', true),

            array('!=2.3.0', '>=1.5.8', true),
            array('<>2.3.0', '>=2.3.0', true),
            array('!=2.3.0', '>=2.3.0.1', true),

            array('<>2.3.0', '<1.5.8', true),
            array('!=2.3.0', '<2.3.0', true),
            array('<>2.3.0', '<2.3.0.1', true),

            array('!=2.3.0', '<=1.5.8', true),
            array('<>2.3.0', '<=2.3.0', true),
            array('!=2.3.0', '<=2.3.0.1', true),

            array('<>2.3.0', '!=1.5.8', true),
            array('!=2.3.0', '!=2.3.0.0', true),
            array('<>2.3.0', '!=2.3.0.1', true),

            array('!=2.3.0', '<>1.5.8', true),
            array('<>2.3.0', '<>2.3', true),
            array('!=2.3.0', '<>2.3.0.1', true),

            array('!=2.3.0', '~1.5.8', true),
            array('!=2.3.0', '~2.3', true),
            array('!=2.3.0', '~2.4.', true),

            array('~2.3.0', '1.5.8', false),

            array('~2.3.5', '>2.3.0', true),
            array('~2.3.5', '>2.3.5', true),
            array('~2.3.5', '>2.3.6', true),

            array('~2.3.5', '>=2.3.0', true),
            array('~2.3.5', '>=2.3.5', true),
            array('~2.3.5', '>=2.3.6', true),

            array('~2.3.5', '<2.3.5', false),
            array('~2.3.5', '<2.3.6', true),
            array('~2.3.5', '<2.4.0', true),

            array('~2.3.5', '<=2.3.5', true),
            array('~2.3.5', '<=2.3.6', true),
            array('~2.3.5', '<=2.4.0', true),

            array('~2.3.5', '!=2.3.0', true),
            array('~2.3.5', '!=2.3.6', true),
            array('~2.3.5', '<>2.4.0', true),

            array('~2.3.5', '~2.3', true),
            array('~2.3.5', '~2.4', false),
            array('~2.3.5', '~2.3', true),
            array('~2.4.5', '~2.3', true),
            array('~2.3', '~2.3.2', true),
            array('~2.4', '~2.3.4', false),
            array('~2.3.5', '~2.3.5', true),
        );
    }
}
