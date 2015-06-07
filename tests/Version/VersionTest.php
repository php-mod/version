<?php

namespace Version;

class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider successfulParsedVersions
     * @param $input
     * @param $expected
     */
    public function testParseSucceeds($input, $expected)
    {
        $this->assertSame(
            $expected,
            (string) Version::parse($input),
            'INPUT: ' . $input
        );
    }

    public function successfulParsedVersions()
    {
        return array(
            'none'               => array('1.0.0',               '1.0.0'),
            'none/2'             => array('1.2.3.4',             '1.2.3.4'),
            'parses state'       => array('1.0.0RC1',            '1.0.0-RC1'),
            'CI parsing'         => array('1.0.0-rC15',          '1.0.0-RC15'),
            'delimiters'         => array('1.0.0.RC.15',         '1.0.0-RC15'),
            'RC uppercase'       => array('1.0.0-rc1',           '1.0.0-RC1'),
            'patch replace'      => array('1.0.0.pl3',           '1.0.0-patch3'),
            'forces w.x.y.z'     => array('1.0',                 '1.0.0'),
            'forces w.x.y.z/2'   => array('0',                   '0.0.0'),
            'parses long'        => array('10.4.13-beta',        '10.4.13-beta'),
            'parses long/2'      => array('10.4.13beta2',        '10.4.13-beta2'),
            'expand shorthand'   => array('10.4.13-b',           '10.4.13-beta'),
            'expand shorthand2'  => array('10.4.13-b5',          '10.4.13-beta5'),
            'strips leading v'   => array('v1.0.0',              '1.0.0'),
            'strips v/datetime'  => array('v20100102',           '20100102'),
            'parses dates y-m'   => array('2010.01',             '2010-01'),
            'parses dates w/ .'  => array('2010.01.02',          '2010-01-02'),
            'parses dates w/ -'  => array('2010-01-02',          '2010-01-02'),
            'parses numbers'     => array('2010-01-02.5',        '2010-01-02-5'),
            'parses dates y.m.Y' => array('2010.1.555',          '2010.1.555'),
            'parses datetime'    => array('20100102-203040',     '20100102-203040'),
            'parses dt+number'   => array('20100102203040-10',   '20100102203040-10'),
            'parses dt+patch'    => array('20100102-203040-p1',  '20100102-203040-patch1'),
        );
    }

    /**
     * @dataProvider failingParsedVersions
     * @param $input
     */
    public function testParseFails($input)
    {
        try {
            Version::parse($input);
            $this->fail('Waiting exception with input: ' . $input);
        } catch (\UnexpectedValueException $e) {
            $this->assertTrue($e instanceof \UnexpectedValueException);
        }
    }

    public function failingParsedVersions()
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
     * @dataProvider stabilityProvider
     * @param $expected
     * @param $version
     */
    public function testParseStability($expected, $version)
    {
        $this->assertSame(
            $expected,
            Version::parse($version)->getVersionStability(),
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
