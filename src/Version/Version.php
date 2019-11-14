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
     * @var string
     */
    private $date;

    /**
     * @var int
     */
    private $dateVersion;

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
     *
     * @return Version
     */
    public static function parse($input)
    {
        if ('' === $input) {
            throw new \UnexpectedValueException('Empty entry');
        }

        $regex = '/^' .
            'v?' .
            '(?:(?P<major>\d+)[-|\.])?' .
            '(?:(?P<minor>\d+)[-|\.])?' .
            '(?:(?P<revision>\d+)\.)?' .
            '(?:(?P<micro>\d+))?' .
            '(?:' . Stability::REGEX . ')?' .
            '$/i';

        if (!preg_match($regex, $input, $matches)) {
            throw new \UnexpectedValueException('Invalid version: ' . $input);
        }

        $numbers = array();

        if (isset($matches['major']) && '' !== $matches['major']) {
            $numbers[] = $matches[1];
        }

        if (isset($matches['minor']) && '' !== $matches['minor']) {
            $numbers[] = $matches[2];
        }

        if (isset($matches['revision']) && '' !== $matches['revision']) {
            $numbers[] = $matches[3];
        }

        if (isset($matches['micro']) && '' !== $matches[4]) {
            $numbers[] = $matches[4];
        }

        if (empty($numbers)) {
            throw new \UnexpectedValueException('Invalid version: ' . $input);
        }

        /* Version numbers */

        $version = new Version($numbers[0]);

        if (
            in_array(strlen($numbers[0]), array(14, 8, 6), true) ||
            (strlen($numbers[0]) === 4 && isset($numbers[1]) && strlen($numbers[1]) === 2)
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

        if (isset($matches['stability']) && '' !== $matches['stability']) {
            $version->setStability(new Stability($matches['stability'], $matches['stabilityVersion']));
        }

        if (isset($matches['date']) && '' !== $matches['date']) {
            $version->setDate($matches['date'], $matches['dateVersion']);
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

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @param int    $dateVersion
     */
    public function setDate($date, $dateVersion)
    {
        $this->date = $date;
        $this->dateVersion = $dateVersion;
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

        if ($stability === 'patch') {
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

        if (($this->micro ?: 0) < ($version->micro ?: 0)) {
            return -1;
        }

        if (($this->micro ?: 0) > ($version->micro ?: 0)) {
            return 1;
        }

        if (($this->date ?: 0) < ($version->date ?: 0)) {
            return -1;
        }

        if (($this->date ?: 0) > ($version->date ?: 0)) {
            return 1;
        }

        if (($this->dateVersion ?: 0) < ($version->dateVersion ?: 0)) {
            return -1;
        }

        if (($this->dateVersion ?: 0) > ($version->dateVersion ?: 0)) {
            return 1;
        }

        return $this->stability->compare($version->stability);
    }

    public function __toString()
    {
        if ($this->regular) {
            $version =
                $this->major . '.' .
                $this->minor . '.' .
                $this->revision;
            if ($this->micro !== null) {
                $version .= '.' . $this->micro;
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

        if ($this->date) {
            $version .= '-' . $this->date;

            if ($this->dateVersion) {
                $version .= '-' . $this->dateVersion;
            }
        }

        if (!$this->stability->isStable()) {
            $version .= '-' . $this->stability;
        }

        return (string)$version;
    }
}
