<?php

namespace Version\Constraint;

class MultiConstraint
{
    public function __construct(array $minMax, $and = true)
    {
        $this->minMax = $minMax;
        $this->and = $and;
    }

    public function __toString()
    {
        return implode($this->and ? ',' : '|', $this->minMax);
    }

    public function match($version)
    {
        if($this->and) {
            foreach($this->minMax as $c) {
                if(!$c->match($version))
                    return false;
            }
            return true;
        } else {
            foreach($this->minMax as $c) {
                if($c->match($version))
                    return true;
            }
            return false;
        }
    }
}
