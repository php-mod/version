<?php

namespace Version;

use Version\Constraint\AnythingConstraint;
use Version\Constraint\MultiConstraint;
use Version\Constraint\SimpleConstraint;

abstract class Constraint
{
    /**
     * Indicate if this constraint matches another constraint
     *
     * @param Constraint $constraint
     *
     * @return bool
     */
    abstract public function matches(Constraint $constraint);

    /**
     * Parse a string and return a Constraint.
     *
     * @param string $input
     *
     * @return Constraint
     *
     * @throws \Exception
     */
    public static function parse($input)
    {
        $input = trim($input);

        if ('' === $input) {
            throw new \UnexpectedValueException('Empty.');
        }

        $inputParts = explode(',', $input);

        if (count($inputParts) > 1) {
            $and = true;
        } else {
            $inputParts = explode('|', $inputParts[0]);
            $and = false;
        }

        if (count($inputParts) > 1) {
            $constraints = array();
            foreach ($inputParts as $constraint) {
                $constraints[] = self::parse($constraint);
            }
            return new MultiConstraint($constraints, $and);
        }

        $inputParts = $inputParts[0];

        $regex = '/^' .
            '(?:([\*|x])\.)?' .
            '(?:([\*|x])\.)?' .
            '(?:([\*|x])\.)?' .
            '(?:([\*|x]))?' .
            '$/';

        if (preg_match($regex, $inputParts, $matches)) {
            return new AnythingConstraint();
        }

        $regex = '/^' .
            '(' . Operator::REGEX . ')? *' .
            '(?:(\d+|\*|x)\.)?' .
            '(?:(\d+|\*|x)\.)?' .
            '(?:(\d+|\*|x)\.)?' .
            '(\d+|\*|x)?' .
            '(?:' . Stability::REGEX . ')?' .
            '$/i';

        if (!preg_match($regex, $inputParts, $matches)) {
            throw new \UnexpectedValueException('Invalid type: ' . $inputParts);
        }

        if (isset($matches[1]) && '' !== $matches[1]) {
            $operator = $matches[1];
        } else {
            $operator = '=';
        }
        $operator = new Operator($operator);

        $parts = array();

        if (isset($matches[2]) && '' !== $matches[2]) {
            $parts[] = $matches[2];
        }
        if (isset($matches[3]) && '' !== $matches[3]) {
            $parts[] = $matches[3];
        }
        if (isset($matches[4]) && '' !== $matches[4]) {
            $parts[] = $matches[4];
        }
        if (isset($matches[5]) && '' !== $matches[5]) {
            $parts[] = $matches[5];
        }

        if ((string)$operator === '~') {
            $end = count($parts);
        } else {
            $end = null;
        }

        while (count($parts) < 4) {
            $parts[] = 0;
        }

        $max = $parts;

        if ($end) {
            if ($end === 1) {
                $max[0]++;
            } elseif ($end === 2) {
                $max[0]++;
                $max[1] = 0;
            } elseif ($end === 3) {
                $max[1]++;
                $max[2] = 0;
            } elseif ($end === 4) {
                $max[2]++;
                $max[3] = 0;
            } else {
                throw new \Exception('Unsupported number of elements.');
            }
        }

        if ($parts[3] === 'x' || $parts[3] === '*') {
            $parts[3] = 0;
            $max[3] = 0;
            $max[2]++;
        }

        if ($parts[2] === 'x' || $parts[2] === '*') {
            $parts[2] = 0;
            $max[2] = 0;
            $max[1]++;
        }

        if ($parts[1] === 'x' || $parts[1] === '*') {
            $parts[1] = 0;
            $max[1] = 0;
            $max[0]++;
        }

        $version = new Version($parts[0]);

        if (isset($parts[1])) {
            $version->setMinor($parts[1]);
        }
        if (isset($parts[2])) {
            $version->setRevision($parts[2]);
        }
        if (isset($parts[3])) {
            $version->setMicro($parts[3]);
        }

        if (isset($matches[6]) && '' !== $matches[6]) {
            if (strtolower($matches[5]) === 'rc') {
                $stability = 'RC';
            } elseif (in_array(strtolower($matches[6]), array('pl', 'patch', 'p'))) {
                $stability = 'patch';
            } elseif (in_array(strtolower($matches[6]), array('beta', 'b'))) {
                $stability = 'beta';
            } elseif (strtolower($matches[6]) === 'stable') {
                $stability = 'stable';
            } else {
                throw new \UnexpectedValueException('Invalid type: ' . $inputParts);
            }

            $version->setStability(new Stability($stability, $matches[7]));
        }

        foreach ($parts as $k => $v) {
            if ($v !== $max[$k]) {
                $maxVersion = new Version($max[0]);

                if (isset($max[1])) {
                    $maxVersion->setMinor($max[1]);
                }

                if (isset($max[2])) {
                    $maxVersion->setRevision($max[2]);
                }

                if (isset($max[3])) {
                    $maxVersion->setMicro($max[3]);
                }

                if ((string)$version === '0.0.0.0') {
                    return new SimpleConstraint(new Operator('<'), $maxVersion);
                }

                if (isset($matches[6]) && strtolower($matches[6]) === 'stable') {
                    $version->setStability(new Stability());
                }

                return new MultiConstraint(array(
                    new SimpleConstraint(new Operator('>='), $version),
                    new SimpleConstraint(new Operator('<'), $maxVersion)
                ));
            }
        }

        return new SimpleConstraint($operator, $version);
    }

    abstract public function isSubsetOf(Constraint $constraint);

    public function isIncluding(Constraint $constraint)
    {
        return $constraint->isSubsetOf($this);
    }
}
