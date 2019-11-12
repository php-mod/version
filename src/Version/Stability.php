<?php

namespace Version;

class Stability
{
    const REGEX = '[-_\.]?(?:(?P<stability>rc|pl|a|alpha|beta|b|patch|stable|p|dev|d)\.?(?P<stabilityVersion>\d*)|' .
    '(?P<date>[0-9]{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[1-2][0-9]|3[0-1]))'
    . ')';

    /**
     * @var string
     */
    private $stability;

    /**
     * @var int
     */
    private $number;

    public function __construct($stability = 'stable', $number = null)
    {
        if ('' === $stability) {
            $stability = 'stable';
        }
        $stability = strtolower($stability);
        switch ($stability) {
            case 'rc':
                $stability = 'RC';
                break;
            case 'patch':
            case 'pl':
            case 'p':
                $stability = 'patch';
                break;
            case 'beta':
            case 'b':
                $stability = 'beta';
                break;
            case 'alpha':
            case 'a':
                $stability = 'alpha';
                break;
            case 'dev':
            case 'd':
                $stability = 'dev';
                break;
        }
        $this->stability = $stability;
        $this->number = $number;
    }

    public function __toString()
    {
        return $this->stability . $this->number;
    }

    public function isStable()
    {
        return $this->stability === 'stable';
    }

    /**
     * @return string
     */
    public function getStability()
    {
        return $this->stability;
    }

    public function compare(Stability $stability)
    {
        if ($this->toInt($this->stability) > $this->toInt($stability->stability)) {
            return 1;
        }
        if ($this->toInt($this->stability) < $this->toInt($stability->stability)) {
            return -1;
        }
        if ($this->number > $stability->number) {
            return 1;
        }
        if ($this->number < $stability->number) {
            return -1;
        }
        return 0;
    }

    private function toInt($stability)
    {
        switch ($stability) {
            case 'dev':
                return 1;
            case 'alpha':
                return 2;
            case 'beta':
                return 3;
            case 'RC':
                return 4;
            case 'stable':
                return 5;
            case 'patch':
                return 6;
            default:
                throw new \InvalidArgumentException('Invalid stability: ' . $stability);
        }
    }
}
