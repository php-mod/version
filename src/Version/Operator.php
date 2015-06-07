<?php

namespace Version;

class Operator
{
    const REGEX = '[<|>|!|=|~]{0,2}';

    private $operator;

    public function __construct($operator)
    {
        if ($operator == '==') {
            $operator = '=';
        }
        if (!in_array($operator, array('=', '<', '>', '<=', '>=', '<>', '!=', '~'))) {
            $propositions = array(
                '~>' => '~',
                '!' => '!='
            );
            throw new \UnexpectedValueException(
                'Invalid operator "' . $operator .
                '", you probably meant to use the "' . $propositions[$operator] .
                '" operator');
        }
        $this->operator = $operator;
    }

    public function __toString()
    {
        return $this->operator;
    }

    /**
     * @param Version $version1
     * @param Version $version2
     * @throws \Exception
     * @return bool
     */
    public function compare(Version $version1, Version $version2)
    {
        if ($this->operator == '=') {
            return $version1->compare($version2) == 0;
        }
        if ($this->operator == '>=') {
            return $version1->compare($version2) >= 0;
        }
        if ($this->operator == '>') {
            return $version1->compare($version2) > 0;
        }
        if ($this->operator == '<=') {
            return $version1->compare($version2) <= 0;
        }
        if ($this->operator == '<') {
            return $version1->compare($version2) < 0;
        }
        if ($this->operator == '!=' || $this->operator == '<>') {
            return $version1->compare($version2) != 0;
        }
        throw new \Exception('Comparison by ' . $this->operator . ' Not implemented yet');
    }
}
