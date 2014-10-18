<?php

namespace Version;

use Version\Constraint\EmptyConstraint;
use Version\Constraint\MultiConstraint;
use Version\Constraint\VersionConstraint;

class VersionParser
{

    const STABILITY_REGEX =
        '(?:[-\.]{0,1}([Rr][Cc]|pl|a|alpha|beta|b|B|patch|p)\.{0,1}(\d*))';

    public function normalize($input)
    {

        if(strlen($input) < 1) {
            throw new \UnexpectedValueException('Empty.');
        }

        $regex = '/^' .
            'v?' .
            '(?:(\d+)[-\.])?' .
            '(?:(\d+)[-\.])?' .
            '(?:(\d+)\.)?' .
            '(?:(\d+))?' .
            '(?:[-\.]{0,1}([Rr][Cc]|pl|beta|b|patch|p)\.{0,1}(\d*))?' .
            '$/';

        preg_match($regex, $input, $matches);

        $parts = array();

        if(isset($matches[1]) && strlen($matches[1]) > 0) $parts[] = $matches[1];
        if(isset($matches[2]) && strlen($matches[2]) > 0) $parts[] = $matches[2];
        if(isset($matches[3]) && strlen($matches[3]) > 0) $parts[] = $matches[3];
        if(isset($matches[4]) && strlen($matches[4]) > 0) $parts[] = $matches[4];

        if(empty($matches)) {
            throw new \UnexpectedValueException('Invalid: ' . $input);
        }

        if(
            isset($parts[0]) &&
            (
                strlen($parts[0]) != 14 &&
                strlen($parts[0]) != 8 &&
                strlen($parts[0]) != 6 &&
                strlen($parts[0]) != 4 ||
                (
                    strlen($parts[0]) == 4 &&
                    isset($parts[1]) &&
                    strlen($parts[1]) != 2
                )
            )
        ) {
            while (count($parts) < 4) {
                $parts[] = 0;
            }
            $glue = '.';
        } else {
            $glue = '-';
        }

        $normalized = implode($glue, $parts);

        if(isset($matches[5]) && strlen($matches[5]) > 0) {
            $stability = '';
            if(strtolower($matches[5]) == 'rc') {
                $stability = 'RC';
            } elseif(in_array(strtolower($matches[5]), array('pl', 'patch', 'p'))) {
                $stability = 'patch';
            } elseif(in_array(strtolower($matches[5]), array('beta', 'b'))) {
                $stability = 'beta';
            }
            $normalized .= '-' . $stability . $matches[6];
        }

        return $normalized;
    }

    public function parseConstraints($input)
    {

        $input = trim($input);

        if(strlen($input) < 1) {
            throw new \UnexpectedValueException('Empty.');
        }

        $array = explode(',', $input);
        if(count($array) == 2) {
            $min = $this->parseConstraints($array[0]);
            $max = $this->parseConstraints($array[1]);
            return new MultiConstraint(array($min, $max));
        }

        $array = explode('|', $input);
        if(count($array) == 2) {
            $min = $this->parseConstraints($array[0]);
            $max = $this->parseConstraints($array[1]);
            return new MultiConstraint(array($min, $max), false);
        }

        $regex = '/^' .
            '(?:([\*|x])\.)?' .
            '(?:([\*|x])\.)?' .
            '(?:([\*|x])\.)?' .
            '(?:([\*|x]))?' .
            '$/';

        if(preg_match($regex, $input, $matches)) {
            return new EmptyConstraint();
        }

        $regex = '/^' .
            '(?:([<|>|!|=|~]*))? *' .
            '(?:(\d+|\*|x)\.)?' .
            '(?:(\d+|\*|x)\.)?' .
            '(?:(\d+|\*|x)\.)?' .
            '(?:(\d+|\*|x))?' .
            '(?:[-\.]{0,1}([Rr][Cc]|pl|[Bb][Ee][Tt][Aa]|b|patch|p|stable)\.{0,1}(\d*))?' .
            '$/';

        preg_match($regex, $input, $matches);

        if(empty($matches)) {
            throw new \UnexpectedValueException('Invalid type: ' . $input);
        }

        $operator = '=';
        if(isset($matches[1]) && strlen($matches[1]) > 0){
            $operator = $matches[1];
            if($operator == '==') $operator = '=';
        }

        if(!in_array($operator, array('=', '<', '>', '<=', '>=', '<>', '!=', '~'))){
            $propositions = array(
                '~>' => '~'
            );
            throw new \UnexpectedValueException(
                'Invalid type: \'' . $input .
                ' contains \'Invalid operator "' . $operator .
                '", you probably meant to use the "' . $propositions[$operator] .
                '" operator');
        }

        $parts = array();

        if(isset($matches[2]) && strlen($matches[2]) > 0) $parts[] = $matches[2];
        if(isset($matches[3]) && strlen($matches[3]) > 0) $parts[] = $matches[3];
        if(isset($matches[4]) && strlen($matches[4]) > 0) $parts[] = $matches[4];
        if(isset($matches[5]) && strlen($matches[5]) > 0) $parts[] = $matches[5];

        if($operator == '~') {
            $end = count($parts);
        } else {
            $end = null;
        }

        while (count($parts) < 4) {
            $parts[] = 0;
        }

        $max = $parts;

        if($end) {
            if($end == 1) {
                $max[0]++;
            } elseif($end == 2) {
                $max[0]++;
                $max[1] = 0;
            } elseif($end == 3) {
                $max[1]++;
                $max[2] = 0;
            } elseif($end == 4) {
                $max[2]++;
                $max[3] = 0;
            } else {
                echo $end;
                die($end);
            }
        }

        if($parts[3] === 'x' || $parts[3] === '*') {
            $parts[3] = 0;
            $max[3] = 0;
            $max[2]++;
        }

        if($parts[2] === 'x' || $parts[2] === '*') {
            $parts[2] = 0;
            $max[2] = 0;
            $max[1]++;
        }

        if($parts[1] === 'x' || $parts[1] === '*') {
            $parts[1] = 0;
            $max[1] = 0;
            $max[0]++;
        }

        $version = implode('.', $parts);

        if(isset($matches[6]) && strlen($matches[6]) > 0) {
            if(strtolower($matches[5]) == 'rc') {
                $stability = '-RC';
            } elseif(in_array(strtolower($matches[6]), array('pl', 'patch', 'p'))) {
                $stability = '-patch';
            } elseif(in_array(strtolower($matches[6]), array('beta', 'b'))) {
                $stability = '-beta';
            } elseif(strtolower($matches[6]) == 'stable') {
                $stability = '';
            } else {
                throw new \UnexpectedValueException('Invalid type: ' . $input);
            }
            $version .= $stability . $matches[7];
        }

        foreach($parts as $k=>$v) {
            if ($v != $max[$k]) {
                if ($input == '<=1.2.3') {
                    print_r($parts);
                    print_r($max);
                    print_r(array_diff($parts, $max));
                    die;
                }
                $max = implode('.', $max);
                if($version == '0.0.0.0') {
                    return new VersionConstraint('<', $max);
                }
                if(isset($matches[6]) && strtolower($matches[6]) == 'stable') {
                    $version .= '-stable';
                }
                return new MultiConstraint(array(
                    new VersionConstraint('>=', $version),
                    new VersionConstraint('<', $max)
                ));
            }
        }

        return new VersionConstraint($operator, $version);

    }

    public static function parseStability($input)
    {
        $regex = '/' . self::STABILITY_REGEX . '/';

        if(preg_match($regex, $input, $matches)) {
            $stability = $matches[1];
            if(in_array(strtolower($stability), array('pl', 'patch'))) {
                return 'stable';
            }
            if(in_array(strtolower($stability), array('b'))) {
                return 'beta';
            }
            if(in_array(strtolower($stability), array('a'))) {
                return 'alpha';
            }
            if(in_array(strtolower($stability), array('rc'))) {
                return 'RC';
            }
            return $stability;
        } else {
            return 'stable';
        }
    }
}
