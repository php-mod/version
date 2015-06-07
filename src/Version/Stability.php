<?php

namespace Version;

class Stability
{
    const REGEX = '[-|_|\.]{0,1}([R|r][C|c]|pl|a|alpha|[B|b][E|e][T|t][A|a]|b|B|patch|stable|p|[D|d][E|e][V|v]|[D|d])\.{0,1}(\d*)';

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
        if (strlen($stability) == 0) {
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
        $this->number    = $number;
    }

    public function __toString()
    {
        return $this->stability . $this->number;
    }

    public function isStable()
    {
        return $this->stability == 'stable';
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
