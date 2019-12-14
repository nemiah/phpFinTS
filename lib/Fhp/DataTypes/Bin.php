<?php

namespace Fhp\DataTypes;

class Bin
{
    /**
     * @var string
     */
    protected $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    /**
     * Sets the binary data.
     *
     * @return $this
     */
    public function setData(string $data)
    {
        $this->string = $data;

        return $this;
    }

    /**
     * Gets the binary data.
     */
    public function getData(): string
    {
        return $this->string;
    }

    /**
     * Convert to string.
     */
    public function toString(): string
    {
        return '@' . strlen($this->string) . '@' . $this->string;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
