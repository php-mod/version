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
        return [
            'none'               => ['1.0.0',               '1.0.0'],
            'none/2'             => ['1.2.3.4',             '1.2.3.4'],
            'parses state'       => ['1.0.0RC1',            '1.0.0-RC1'],
            'CI parsing'         => ['1.0.0-rC15',          '1.0.0-RC15'],
            'delimiters'         => ['1.0.0.RC.15',         '1.0.0-RC15'],
            'RC uppercase'       => ['1.0.0-rc1',           '1.0.0-RC1'],
            'patch replace'      => ['1.0.0.pl3',           '1.0.0-patch3'],
            'forces w.x.y.z'     => ['1.0',                 '1.0.0'],
            'forces w.x.y.z/2'   => ['0',                   '0.0.0'],
            'parses long'        => ['10.4.13-beta',        '10.4.13-beta'],
            'parses long/2'      => ['10.4.13beta2',        '10.4.13-beta2'],
            'expand shorthand'   => ['10.4.13-b',           '10.4.13-beta'],
            'expand shorthand2'  => ['10.4.13-b5',          '10.4.13-beta5'],
            'strips leading v'   => ['v1.0.0',              '1.0.0'],
            'strips v/datetime'  => ['v20100102',           '20100102'],
            'parses dates y-m'   => ['2010.01',             '2010-01'],
            'parses dates w/ .'  => ['2010.01.02',          '2010-01-02'],
            'parses dates w/ -'  => ['2010-01-02',          '2010-01-02'],
            'parses numbers'     => ['2010-01-02.5',        '2010-01-02-5'],
            'parses dates y.m.Y' => ['2010.1.555',          '2010.1.555'],
            'parses datetime'    => ['20100102-203040',     '20100102-203040'],
            'parses dt+number'   => ['20100102203040-10',   '20100102203040-10'],
            'parses dt+patch'    => ['20100102-203040-p1',  '20100102-203040-patch1'],
        ];
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
        return [
            'empty '            => [''],
            'invalid chars'     => ['a'],
            'invalid type'      => ['1.0.0-meh'],
            'too many bits'     => ['1.0.0.0.0'],
            'non-dev arbitrary' => ['feature-foo'],
        ];
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
        return [
            ['stable', '1'],
            ['stable', '1.0'],
            ['stable', '3.2.1'],
            ['stable', 'v3.2.1'],
            ['RC',     '3.0-RC2'],
            ['stable', '3.1.2-pl2'],
            ['stable', '3.1.2-patch'],
            ['alpha',  '3.1.2-alpha5'],
            ['beta',   '3.1.2-beta'],
            ['beta',   '2.0B1'],
            ['alpha',  '1.2.0a1'],
            ['alpha',  '1.2_a1'],
            ['RC',     '2.0.0rc1']
        ];
    }
}
