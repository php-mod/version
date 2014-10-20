<?php

namespace Version\Constraint;

use Version\Constraint;

class AnythingConstraint extends Constraint
{
    public function __toString()
    {
        return '*';
    }

    public function matches(Constraint $constraint)
    {
        return true;
    }

    public function isSubsetOf(Constraint $constraint)
    {
        throw new \Exception('Constraint comparison of *  with constraint ' .
            $constraint . ' Not implemented yet');
    }
}
