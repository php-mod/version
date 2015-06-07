<?php

namespace Version\Constraint;

use Version\Constraint;

class MultiConstraint extends Constraint
{
    /**
     * @var Constraint[]
     */
    private $constraints;

    public function __construct(array $constraints, $and = true)
    {
        $this->constraints = $constraints;
        $this->and = $and;
    }

    public function __toString()
    {
        return implode($this->and ? ',' : '|', $this->constraints);
    }

    public function matches(Constraint $constraint)
    {
        if ($this->and) {
            foreach ($this->constraints as $c) {
                if (!$c->matches($constraint)) {
                    return false;
                }
            }
            return true;
        } else {
            foreach ($this->constraints as $c) {
                if ($c->matches($constraint)) {
                    return true;
                }
            }
            return false;
        }
    }

    public function isSubsetOf(Constraint $constraint)
    {
        if ($constraint instanceof SimpleConstraint) {
            foreach ($this->constraints as $child) {
                if ($child->isSubsetOf($constraint)) {
                    return true;
                }
            }
            return false;
        }
        if ($constraint instanceof MultiConstraint) {
            if (count($this->constraints) == 2) {
                if (count($constraint->constraints) == 2) {
                    $min1 = $this->constraints[0];
                    $max1 = $this->constraints[1];
                    $min2 = $constraint->constraints[0];
                    $max2 = $constraint->constraints[1];
                    if (
                        $min1 instanceof SimpleConstraint &&
                        $min2 instanceof SimpleConstraint &&
                        $max1 instanceof SimpleConstraint &&
                        $max2 instanceof SimpleConstraint
                    ) {
                        if (
                            in_array((string) $min1->getOperator(), array('>', '>=')) &&
                            in_array((string) $min2->getOperator(), array('>', '>=')) &&
                            in_array((string) $max1->getOperator(), array('<', '<=')) &&
                            in_array((string) $max2->getOperator(), array('<', '<='))
                        ) {
                            return
                                $min1->isSubsetOf($min2) &&
                                $max1->isSubsetOf($max2);
                        }
                    }
                }
            }
        }
        throw new \Exception('Constraint comparison by ' .
            $this . ' with constraint ' . $constraint .
            ' Not implemented yet');
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }
}
