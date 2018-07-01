<?php

namespace SerendipityHQ\Component\Stopwatch\Properties;

/**
 * Used by Section and Event to save the origin time and memory.
 */
class Origin
{
    /** @var Time $originTime */
    private $originTime;

    /** @var Memory $originMemory */
    private $originMemory;

    /** @var int $originMemoryCurrent Of the memory assigned to PHP, the amount of memory currently consumed by the script */
    private $originMemoryCurrent;

    /** @var int $originMemoryPeak The max amount of memory assigned to PHP */
    private $originMemoryPeak;

    /** @var int $originMemoryPeakEmalloc The max amount of memory assigned to PHP and used by emalloc() */
    private $originMemoryPeakEmalloc;

    /**
     * Sets the current time and memories.
     */
    private function __construct()
    {
        $this->originTime              = microtime(true);
        $this->originMemory            = memory_get_usage(true);
        $this->originMemoryCurrent     = memory_get_usage();
        $this->originMemoryPeak        = memory_get_peak_usage(true);
        $this->originMemoryPeakEmalloc = memory_get_peak_usage();
    }

    /**
     * @return Time
     */
    public function getOriginTime(): Time
    {
        return $this->originTime;
    }

    /**
     * @return Memory
     */
    public function getOriginMemory(): Memory
    {
        return $this->originMemory;
    }

    /**
     * Of the memory assigned to PHP, gets the amount of memory currently used by the script.
     *
     * @return int
     */
    public function getOriginMemoryCurrent(): int
    {
        return $this->originMemoryCurrent;
    }

    /**
     * Gets the max amount of memory assigned to PHP.
     *
     * @return int
     */
    public function getOriginMemoryPeak(): int
    {
        return $this->originMemoryPeak;
    }

    /**
     * Gets the max amount of memory used by emalloc().
     *
     * @return int
     */
    public function getOriginMemoryPeakEmalloc(): int
    {
        return $this->originMemoryPeakEmalloc;
    }
}
