<?php

namespace ChickenTikkaMasala\GPIO\Modes;

use ChickenTikkaMasala\GPIO\GPIO;

/**
 * Class Write
 * @package ChickenTikkaMasala\GPIO\Modes
 */
class Write extends GPIO
{
    /**
     * @param int $value
     * @return mixed
     */
    public function set($value = 0)
    {
        $this->lastValue = (int)($value >1) ? 1: (($value <1) ? 0 : $value);
        return $this->write();
    }

    /**
     * @return int
     */
    public function get()
    {
        return $this->getPrevious();
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'out';
    }
}