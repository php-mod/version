<?php

namespace Version\Constraint;

class EmptyConstraint extends AbstractConstraint
{
    public function __toString()
    {
        return '*';
    }

    public function match()
    {
        return true;
    }
}
