<?php

namespace Version;

use UnexpectedValueException;
use Version\Constraint\EmptyConstraint;
use Version\Constraint\MultiConstraint;
use Version\Constraint\VersionConstraint;

/**
 * Class VersionParserTest
 *
 * Tests are inspired from Composer Version Tests
 * @package Version
 */
class VersionParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider successfulNormalizedVersions
     * @param $input
     * @param $expected
     */
    public function testNormalizeSucceeds($input, $expected)
    {
        $parser = new VersionParser;
        $this->assertSame(
            $expected,
            $parser->normalize($input),
            'INPUT: ' . $input
        );
    }

    public function successfulNormalizedVersions()
    {
        return array(
            'none'               => array('1.0.0',               '1.0.0.0'),
            'none/2'             => array('1.2.3.4',             '1.2.3.4'),
            'parses state'       => array('1.0.0RC1',            '1.0.0.0-RC1'),
            'CI parsing'         => array('1.0.0-rC15',          '1.0.0.0-RC15'),
            'delimiters'         => array('1.0.0.RC.15',         '1.0.0.0-RC15'),
            'RC uppercase'       => array('1.0.0-rc1',           '1.0.0.0-RC1'),
            'patch replace'      => array('1.0.0.pl3',           '1.0.0.0-patch3'),
            'forces w.x.y.z'     => array('1.0',                 '1.0.0.0'),
            'forces w.x.y.z/2'   => array('0',                   '0.0.0.0'),
            'parses long'        => array('10.4.13-beta',        '10.4.13.0-beta'),
            'parses long/2'      => array('10.4.13beta2',        '10.4.13.0-beta2'),
            'expand shorthand'   => array('10.4.13-b',           '10.4.13.0-beta'),
            'expand shorthand2'  => array('10.4.13-b5',          '10.4.13.0-beta5'),
            'strips leading v'   => array('v1.0.0',              '1.0.0.0'),
            'strips v/datetime'  => array('v20100102',           '20100102'),
            'parses dates y-m'   => array('2010.01',             '2010-01'),
            'parses dates w/ .'  => array('2010.01.02',          '2010-01-02'),
            'parses dates w/ -'  => array('2010-01-02',          '2010-01-02'),
            'parses numbers'     => array('2010-01-02.5',        '2010-01-02-5'),
            'parses dates y.m.Y' => array('2010.1.555',          '2010.1.555.0'),
            'parses datetime'    => array('20100102-203040',     '20100102-203040'),
            'parses dt+number'   => array('20100102203040-10',   '20100102203040-10'),
            'parses dt+patch'    => array('20100102-203040-p1',  '20100102-203040-patch1'),
        );
    }

    /**
     * @dataProvider failingNormalizedVersions
     * @expectedException UnexpectedValueException
     * @param $input
     */
    public function testNormalizeFails($input)
    {
        $parser = new VersionParser;
        $parser->normalize($input);
    }

    public function failingNormalizedVersions()
    {
        return array(
            'empty '            => array(''),
            'invalid chars'     => array('a'),
            'invalid type'      => array('1.0.0-meh'),
            'too many bits'     => array('1.0.0.0.0'),
            'non-dev arbitrary' => array('feature-foo'),
        );
    }

    /**
     * @dataProvider simpleConstraints
     * @param $input
     * @param $expected
     */
    public function testParseConstraintsSimple($input, $expected)
    {
        $parser = new VersionParser;
        $this->assertSame(
            (string) $expected,
            (string) $parser->parseConstraints($input),
            'INPUT: ' . $input
        );
    }

    public function simpleConstraints()
    {
        return array(
            'match any'            => array('*',               new EmptyConstraint()),
            'match any/2'          => array('*.*',             new EmptyConstraint()),
            'match any/3'          => array('*.x.*',           new EmptyConstraint()),
            'match any/4'          => array('x.x.x.*',         new EmptyConstraint()),
            'not equal'            => array('<>1.0.0',         new VersionConstraint('<>', '1.0.0.0')),
            'not equal/2'          => array('!=1.0.0',         new VersionConstraint('!=', '1.0.0.0')),
            'greater than'         => array('>1.0.0',          new VersionConstraint('>',  '1.0.0.0')),
            'lesser than'          => array('<1.2.3.4',        new VersionConstraint('<',  '1.2.3.4')),
            'less/eq than'         => array('<=1.2.3',         new VersionConstraint('<=', '1.2.3.0')),
            'great/eq than'        => array('>=1.2.3',         new VersionConstraint('>=', '1.2.3.0')),
            'equals'               => array('=1.2.3',          new VersionConstraint('=',  '1.2.3.0')),
            'double equals'        => array('==1.2.3',         new VersionConstraint('=',  '1.2.3.0')),
            'no op means eq'       => array('1.2.3',           new VersionConstraint('=',  '1.2.3.0')),
            'completes version'    => array('=1.0',            new VersionConstraint('=',  '1.0.0.0')),
            'shorthand beta'       => array('1.2.3b5',         new VersionConstraint('=',  '1.2.3.0-beta5')),
            'accepts spaces'       => array('>= 1.2.3',        new VersionConstraint('>=', '1.2.3.0')),
            'lesser than override' => array('<1.2.3.4-stable', new VersionConstraint('<',  '1.2.3.4')),
        );
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Invalid operator "~>", you probably meant to use the "~" operator
     */
    public function testParseConstraintsNudgesRubyDevsTowardsThePathOfRighteousness()
    {
        $parser = new VersionParser;
        $parser->parseConstraints('~>1.2');
    }

    /**
     * @dataProvider wildcardConstraints
     * @param $input
     * @param $min
     * @param $max
     */
    public function testParseConstraintsWildcard($input, $min, $max)
    {
        $parser = new VersionParser;
        if ($min) {
            $expected = new MultiConstraint(array($min, $max));
        } else {
            $expected = $max;
        }

        $this->assertSame((string) $expected, (string) $parser->parseConstraints($input));
    }

    public function wildcardConstraints()
    {
        return array(
            array('2.*',     new VersionConstraint('>=', '2.0.0.0'), new VersionConstraint('<', '3.0.0.0')),
            array('2.0.*',   new VersionConstraint('>=', '2.0.0.0'), new VersionConstraint('<', '2.1.0.0')),
            array('2.2.x',   new VersionConstraint('>=', '2.2.0.0'), new VersionConstraint('<', '2.3.0.0')),
            array('2.1.3.*', new VersionConstraint('>=', '2.1.3.0'), new VersionConstraint('<', '2.1.4.0')),
            array('20.*',    new VersionConstraint('>=', '20.0.0.0'), new VersionConstraint('<', '21.0.0.0')),
            array('2.10.x',  new VersionConstraint('>=', '2.10.0.0'), new VersionConstraint('<', '2.11.0.0')),
            array('0.*',     null, new VersionConstraint('<', '1.0.0.0')),
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
        $parser = new VersionParser;
        if ($min) {
            $expected = new MultiConstraint(array($min, $max));
        } else {
            $expected = $max;
        }

        $this->assertSame(
            (string) $expected,
            (string) $parser->parseConstraints($input),
            'INPUT: ' . $input
        );
    }

    public function tildeConstraints()
    {
        return array(
            array('~1',       new VersionConstraint('>=', '1.0.0.0'), new VersionConstraint('<', '2.0.0.0')),
            array('~1.0',     new VersionConstraint('>=', '1.0.0.0'), new VersionConstraint('<', '2.0.0.0')),
            array('~1.0.0',     new VersionConstraint('>=', '1.0.0.0'), new VersionConstraint('<', '1.1.0.0')),
            array('~1.2',     new VersionConstraint('>=', '1.2.0.0'), new VersionConstraint('<', '2.0.0.0')),
            array('~1.2.3',   new VersionConstraint('>=', '1.2.3.0'), new VersionConstraint('<', '1.3.0.0')),
            array('~1.2.3.4', new VersionConstraint('>=', '1.2.3.4'), new VersionConstraint('<', '1.2.4.0')),
            array('~1.2-beta',new VersionConstraint('>=', '1.2.0.0-beta'), new VersionConstraint('<', '2.0.0.0')),
            array('~1.2-b2',  new VersionConstraint('>=', '1.2.0.0-beta2'), new VersionConstraint('<', '2.0.0.0')),
            array('~1.2-BETA2', new VersionConstraint('>=', '1.2.0.0-beta2'), new VersionConstraint('<', '2.0.0.0')),
            array('~1.2.2', new VersionConstraint('>=', '1.2.2.0'), new VersionConstraint('<', '1.3.0.0')),
            array('~1.2.2-stable', new VersionConstraint('>=', '1.2.2.0-stable'), new VersionConstraint('<', '1.3.0.0')),
        );
    }

    public function testParseConstraintsMulti()
    {
        $parser = new VersionParser;
        $first = new VersionConstraint('>', '2.0.0.0');
        $second = new VersionConstraint('<=', '3.0.0.0');
        $multi = new MultiConstraint(array($first, $second));
        $this->assertSame((string) $multi, (string) $parser->parseConstraints('>2.0,<=3.0'));
    }

    public function testParseConstraintsMultiDisjunctiveHasPrioOverConjuctive()
    {
        $parser = new VersionParser;
        $first = new VersionConstraint('>', '2.0.0.0');
        $second = new VersionConstraint('<', '2.0.5.0');
        $third = new VersionConstraint('>', '2.0.6.0');
        $multi1 = new MultiConstraint(array($first, $second));
        $multi2 = new MultiConstraint(array($multi1, $third), false);
        $this->assertSame((string) $multi2, (string) $parser->parseConstraints('>2.0,<2.0.5 | >2.0.6'));
    }

    public function testParseConstraintsMultiWithStabilities()
    {
        $parser = new VersionParser;
        $first = new VersionConstraint('>', '2.0.0.0');
        $second = new VersionConstraint('<=', '3.0.0.0');
        $multi = new MultiConstraint(array($first, $second));
        $this->assertSame((string) $multi, (string) $parser->parseConstraints('>2.0,<=3.0'));
    }

    /**
     * @dataProvider failingConstraints
     * @expectedException UnexpectedValueException
     * @param $input
     */
    public function testParseConstraintsFails($input)
    {
        $parser = new VersionParser;
        $parser->parseConstraints($input);
    }

    public function failingConstraints()
    {
        return array(
            'empty '            => array(''),
            'invalid version'   => array('1.0.0-meh'),
        );
    }

    /**
     * @dataProvider stabilityProvider
     * @param $expected
     * @param $version
     */
    public function testParseStability($expected, $version)
    {
        $this->assertSame(
            $expected,
            VersionParser::parseStability($version),
            'INPUT: ' . $version
        );
    }

    public function stabilityProvider()
    {
        return array(
            array('stable', '1'),
            array('stable', '1.0'),
            array('stable', '3.2.1'),
            array('stable', 'v3.2.1'),
            array('RC',     '3.0-RC2'),
            array('stable', '3.1.2-pl2'),
            array('stable', '3.1.2-patch'),
            array('alpha',  '3.1.2-alpha5'),
            array('beta',   '3.1.2-beta'),
            array('beta',   '2.0B1'),
            array('alpha',  '1.2.0a1'),
            array('alpha',  '1.2_a1'),
            array('RC',     '2.0.0rc1')
        );
    }
}
