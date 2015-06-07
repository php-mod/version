<?php

namespace Version\Constraint;

use Version\Constraint;
use Version\Operator;
use Version\Version;

class SimpleConstraint extends Constraint
{
    /**
     * @var Operator
     */
    private $operator;

    /**
     * @var Version
     */
    private $version;

    /**
     * @param Operator $operator
     * @param Version $version
     */
    public function __construct(Operator $operator, Version $version)
    {
        $this->operator = $operator;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->operator .
        $this->version;
    }

    public function matches(Constraint $constraint)
    {
        if ($constraint instanceof AnythingConstraint) {
            return true;
        }
        if ($constraint instanceof SimpleConstraint) {
            if ((string) $this->operator == '=' &&
                (string) $constraint->operator == '=') {
                return $this->version->compare($constraint->version) == 0;
            }
            if ((string) $this->operator == '!=' ||
                (string) $this->operator == '<>') {
                if ((string) $constraint->operator == '=') {
                    return $this->version->compare($constraint->version) != 0;
                }
                return true;
            }
            if ((string) $constraint->operator == '!=' ||
                (string) $constraint->operator == '<>') {
                if ((string) $this->operator == '=') {
                    return $this->version->compare($constraint->version) != 0;
                }
                return true;
            }
            if ((string) $this->operator == '>') {
                if ((string) $constraint->operator == '<' ||
                    (string) $constraint->operator == '<='
                ) {
                    return $this->version->compare($constraint->version) < 0;
                }
            }
            if ((string) $this->operator == '>=') {
                if ((string) $constraint->operator == '<') {
                    return $this->version->compare($constraint->version) < 0;
                }
            }
            if ((string) $this->operator == '<') {
                if ((string) $constraint->operator == '>' ||
                    (string) $constraint->operator == '>='
                ) {
                    return $this->version->compare($constraint->version) > 0;
                }
            }
            if ((string) $this->operator == '<=') {
                if ((string) $constraint->operator == '>') {
                    return $this->version->compare($constraint->version) > 0;
                }
            }
            if ((string) $this->operator == '>=' &&
                (string) $constraint->operator == '<=') {
                return $this->version->compare($constraint->version) <= 0;
            }
            if ((string) $this->operator == '<=' &&
                (string) $constraint->operator == '>=') {
                return $this->version->compare($constraint->version) >= 0;
            }
            return
                $this->isSubsetOf($constraint) ||
                $constraint->isSubsetOf($this);
        }
        if ($constraint instanceof MultiConstraint) {
            return $constraint->matches($this);
        }
        return false;
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return Operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param Constraint $constraint
     * @throws \Exception
     * @return bool
     */
    public function isSubsetOf(Constraint $constraint)
    {
        if ($constraint instanceof SimpleConstraint) {
            // = , *
            if ((string) $this->getOperator() == '=') {
                return $constraint->getOperator()->compare(
                    $this->getVersion(), $constraint->getVersion()
                );
            }
            // * except = VS =
            if ((string) $constraint->getOperator() == '=') {
                return false;
            }
            // > | >= VS * expect =
            if (in_array((string) $this->getOperator(), array('>', '>='))) {
                // > VS > OR >= VS >= OR > VS >=
                if ((string) $constraint->getOperator() == (string) $this->getOperator() ||
                    (string) $constraint->getOperator() == '>='
                ) {
                    return $this->version->compare($constraint->version) >= 0;
                }
                // > | >= VS <,<=
                if (in_array((string) $constraint->getOperator(), array('<', '<='))) {
                    return false;
                }
                // >= VS * expect =,>=,<,<=
                if ((string) $this->getOperator() == '>=') {
                    return $this->version->compare($constraint->version) > 0;
                }
                if ((string) $this->getOperator() == '>') {
                    return $this->version->compare($constraint->version) >= 0;
                }
            }
            if (in_array((string) $this->getOperator(), array('<', '<='))) {
                if ((string) $constraint->getOperator() == (string) $this->getOperator() ||
                    (string) $constraint->getOperator() == '<='
                ) {
                    return $this->version->compare($constraint->version) <= 0;
                }
                if (in_array((string) $constraint->getOperator(), array('>', '>='))) {
                    return false;
                }
                if ((string) $this->getOperator() == '<=') {
                    return $this->version->compare($constraint->version) < 0;
                }
                if ((string) $this->getOperator() == '<') {
                    return $this->version->compare($constraint->version) <= 0;
                }
            }
            if (in_array((string) $this->getOperator(), array('<>', '!='))) {
                if (in_array((string) $constraint->getOperator(), array('<>', '!='))) {
                    return $this->version->compare($constraint->version) == 0;
                }
                return false;
            }
        } elseif ($constraint instanceof MultiConstraint) {
            foreach ($constraint->getConstraints() as $child) {
                if (! $this->isSubsetOf($child)) {
                    return false;
                }
            }
            return true;
        }
        throw new \Exception('Constraint comparison by ' .
            $this->operator . ' with constraint ' . $constraint .
            ' Not implemented yet');
    }
}
