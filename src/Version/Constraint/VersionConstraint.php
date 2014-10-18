<?php

namespace Version\Constraint;

use Version\VersionParser;

class VersionConstraint extends AbstractConstraint
{
    private $operator;
    private $version;

    public function __construct($operator, $version)
    {
        $this->operator = $operator;
        if(!is_string($version)) {
            throw new \InvalidArgumentException('Arg 2 must be a string');
        }
        $this->version = $version;
    }

    public function __toString()
    {
        return $this->operator .
            $this->version;
    }

    public function match($version)
    {
        $parser = new VersionParser();
        $version = $parser->normalize($version);
        return version_compare($version, $parser->normalize($this->version), $this->operator);
    }
}
