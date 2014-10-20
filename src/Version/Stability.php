<?php

namespace Version;

use JsonSchema\Exception\InvalidArgumentException;

class Stability
{

    const REGEX = '[-|_|\.]{0,1}([R|r][C|c]|pl|a|alpha|[B|b][E|e][T|t][A|a]|b|B|patch|stable|p)\.{0,1}(\d*)';

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

        if (strtolower($stability) == 'rc') {
            $stability = 'RC';
        } elseif (in_array(strtolower($stability), array('pl', 'patch', 'p'))) {
            $stability = 'patch';
        } elseif (in_array(strtolower($stability), array('beta', 'b'))) {
            $stability = 'beta';
        } elseif (in_array(strtolower($stability), array('a'))) {
            $stability = 'alpha';
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
        if($this->toInt($this->stability) > $this->toInt($stability->stability)) {
            return 1;
        }
        if($this->toInt($this->stability) < $this->toInt($stability->stability)) {
            return -1;
        }
        if($this->number > $stability->number) {
            return 1;
        }
        if($this->number < $stability->number) {
            return -1;
        }
        return 0;
    }

    private function toInt($stability)
    {
        switch($stability) {
            case 'alpha': return 1;
            case 'beta': return 2;
            case 'RC': return 3;
            case 'stable': return 4;
            case 'patch': return 5;
            default: throw new InvalidArgumentException('Invalid stability: ' . $stability);
        }
    }
}
