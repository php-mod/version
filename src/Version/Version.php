<?php

namespace Version;

class Version
{
    /**
     * @var int
     */
    private $major;

    /**
     * @var int
     */
    private $minor;

    /**
     * @var int
     */
    private $revision;

    /**
     * @var int
     */
    private $micro;

    /**
     * @var Stability
     */
    private $stability;

    /**
     * @var bool
     */
    private $regular = true;

    public function __construct(
        $major, $minor = 0, $revision = 0, $micro = null, Stability $stability = null)
    {
        $this->setMajor($major);
        $this->setMinor($minor);
        $this->setRevision($revision);
        $this->setMicro($micro);
        if ($stability === null) {
            $stability = new Stability();
        }
        $this->setStability($stability);
    }

    /**
     * @param string $input
     * @return Version
     */
    public static function parse($input)
    {
        if (strlen($input) < 1) {
            throw new \UnexpectedValueException('Empty entry');
        }

        $regex = '/^' .
            'v?' .
            '(?:(\d+)[-|\.])?' .
            '(?:(\d+)[-|\.])?' .
            '(?:(\d+)\.)?' .
            '(?:(\d+))?' .
            '(?:' . Stability::REGEX . ')?' .
            '$/';

        if (!preg_match($regex, $input, $matches)) {
            throw new \UnexpectedValueException('Invalid version: ' . $input);
        }

        $numbers = array();

        if (isset($matches[1]) && strlen($matches[1]) > 0) {
            $numbers[] = $matches[1];
        }
        if (isset($matches[2]) && strlen($matches[2]) > 0) {
            $numbers[] = $matches[2];
        }
        if (isset($matches[3]) && strlen($matches[3]) > 0) {
            $numbers[] = $matches[3];
        }
        if (isset($matches[4]) && strlen($matches[4]) > 0) {
            $numbers[] = $matches[4];
        }

        if (empty($numbers)) {
            throw new \UnexpectedValueException('Invalid version: ' . $input);
        }

        /* Version numbers */

        $version = new Version($numbers[0]);

        if (
            strlen($numbers[0]) == 14 ||
            strlen($numbers[0]) == 8 ||
            strlen($numbers[0]) == 6 ||
            (
                strlen($numbers[0]) == 4 &&
                isset($numbers[1]) &&
                strlen($numbers[1]) == 2
            )
        ) {
            $version->setRegularity(false);
        }

        if (isset($numbers[1])) {
            $version->setMinor($numbers[1]);
        }

        if (isset($numbers[2])) {
            $version->setRevision($numbers[2]);
        }
        if (isset($numbers[3])) {
            $version->setMicro($numbers[3]);
        }

        /* Stability */

        if (isset($matches[5]) && strlen($matches[5]) > 0) {
            $version->setStability(new Stability($matches[5], $matches[6]));
        }

        return $version;
    }

    /**
     * @param boolean $regular
     */
    public function setRegularity($regular)
    {
        $this->regular = $regular;
    }

    /**
     * @return int
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * @param int $major
     */
    public function setMajor($major)
    {
        $this->major = $major;
    }

    /**
     * @return int
     */
    public function getMinor()
    {
        return $this->minor;
    }

    /**
     * @param int $minor
     */
    public function setMinor($minor)
    {
        $this->minor = $minor;
    }

    /**
     * @return int
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * @param int $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    /**
     * @return int
     */
    public function getMicro()
    {
        return $this->micro;
    }

    /**
     * @param int $micro
     */
    public function setMicro($micro)
    {
        $this->micro = $micro;
    }

    /**
     * @return Stability
     */
    public function getStability()
    {
        return $this->stability;
    }

    /**
     * @param Stability $stability
     */
    public function setStability($stability)
    {
        $this->stability = $stability;
    }

    public function __toString()
    {
        if ($this->regular) {
            $version =
                $this->major . '.' .
                $this->minor . '.' .
                $this->revision;
            if ($this->micro !== null) {
                $version .= '.' . (int)$this->micro;
            }
        } else {
            $version = $this->major;
            if ($this->minor) {
                $version .= '-' . $this->minor;
            }
            if ($this->revision) {
                $version .= '-' . $this->revision;
            }
            if ($this->micro) {
                $version .= '-' . $this->micro;
            }
        }
        if (!$this->stability->isStable()) {
            $version .= '-' . (string)$this->stability;
        }
        return $version;
    }

    /**
     * @return boolean
     */
    public function isRegular()
    {
        return $this->regular;
    }

    public function getVersionStability()
    {
        $stability = $this->getStability()->getStability();
        if ($stability == 'patch') {
            return 'stable';
        }
        return $stability;
    }

    public function compare(Version $version)
    {
        if ($this->major < $version->major) {
            return -1;
        }
        if ($this->major > $version->major) {
            return 1;
        }

        if ($this->minor < $version->minor) {
            return -1;
        }
        if ($this->minor > $version->minor) {
            return 1;
        }

        if ($this->revision < $version->revision) {
            return -1;
        }
        if ($this->revision > $version->revision) {
            return 1;
        }

        if ($this->micro < $version->micro) {
            return -1;
        }
        if ($this->micro > $version->micro) {
            return 1;
        }

        return $this->stability->compare($version->stability);
    }
}
