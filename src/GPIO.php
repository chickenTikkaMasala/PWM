<?php

namespace ChickenTikkaMasala\GPIO;

use ChickenTikkaMasala\GPIO\Exception\GPIOCommandNotFoundException;

/**
 * Class GPIO
 * @package ChickenTikkaMasala\GPIO
 */
Abstract class GPIO
{
    /**
     * @var int
     */
    private $pin = 0;

    /**
     * @var int
     */
    protected $lastValue = 0;

    /**
     * @var int
     */
    protected static $max = 1;

    /**
     * @var int
     */
    protected static $min = 0;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    private $availableOptions = [
        'g',
        '1',
        'p',
        'x',
    ];

    /**
     * @var string
     */
    private $mode = '';

    /**
     * @var string
     */
    private $method = '';

    /**
     * GPIO constructor.
     * @param $pin
     * @param string $defaultState
     * @param array $options
     */
    public function __construct($pin, $defaultState = 'OFF', $options = [])
    {
        $this->pin = $pin;
        $this->mode = $this->getMode();
        $this->method = $this->getMethod();
        $this->setOptions($options);
        $this->executeMode();
        $this->set($defaultState);
    }

    /**
     * @param $options
     */
    protected function setOptions(Array $options = [])
    {
        foreach($options as $option)
        {
            if (in_array($option, $this->availableOptions)) {
                $this->options[] = '-'.$option;
            }
        }
    }

    /**
     * @param int $value
     * @return string
     */
    public function set($value = 0)
    {
        if (strtoupper($value) == 'ON' || strtoupper($value) == 'HIGH' || strtoupper($value) == 'UP') $value = static::$max;
        if (strtoupper($value) == 'OFF' || strtoupper($value) == 'LOW' || strtoupper($value) == 'DOWN') $value = static::$min;

        if ($value > static::$max) $value = static::$max;
        if ($value < static::$min) $value = static::$min;

        $this->lastValue = $value;
        return $this->execute();
    }

    /**
     * @param bool $read
     * @return string
     * @throws GPIOCommandNotFoundException
     */
    protected function execute($read = false)
    {
        $value = shell_exec('gpio ' . $this->mode . '  ' . $this->pin . ' ' . (($read) ? '' : $this->lastValue));

        /** this doesn't work */
        if (preg_match("/command\snot\sfound/", $value)) {
            throw new GPIOCommandNotFoundException();
        }

        return $value;

    }

    /**
     * @return string
     * @throws GPIOCommandNotFoundException
     */
    protected function executeMode()
    {
        $value = shell_exec('gpio '.implode(' ', $this->options).' mode '.$this->pin.' '.$this->method);

        /** this doesn't work */
        if (preg_match("/command\snot\sfound/", $value)) {
            throw new GPIOCommandNotFoundException();
        }

        return $value;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return [
            'pin' => $this->pin,
            'mode' => $this->mode,
            'method' => $this->method,
            'value' => $this->getPrevious(),
            'class' => get_class($this),
            'options' => implode(', ', $this->options),
        ];
    }

    public function __destruct()
    {
        $this->set();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->lastValue;
    }

    /**
     * @return int
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @return int
     */
    public function getMaxValue()
    {
        return static::$max;
    }

    /**
     * @return int
     */
    public function getMinimum()
    {
        return static::$min;
    }

    /**
     * @return string
     */
    protected function getMode()
    {
        $class = explode("\\", get_class($this));
        $class = end($class);
        return strtolower($class);
    }

    abstract public function getMethod();

    /**
     * @return int
     */
    public function getPrevious()
    {
        return $this->lastValue;
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->lastValue = $this->execute(true);
    }

}
