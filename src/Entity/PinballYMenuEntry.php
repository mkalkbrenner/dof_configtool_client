<?php

namespace App\Entity;

class PinballYMenuEntry
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $ipdbid;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $manufacturer;

    /**
     * @var string
     */
    private $year;

    /**
     * @var string
     */
    private $rating;

    /**
     * @var string
     */
    private $rom;

    public function __construct(string $name, \SimpleXMLElement $element)
    {
        $this->name = $name;
        $this->ipdbid = (string) $element->ipdbid;
        $this->description = (string) $element->description;
        $this->type = (string) $element->type;
        $this->manufacturer = (string) $element->manufacturer;
        $this->year = (string) $element->year;
        $this->rating = (string) $element->rating;
        $this->rom = (string) $element->rom;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PinballYMenuEntry
     */
    public function setName(string $name): PinballYMenuEntry
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getIpdbid(): int
    {
        return $this->ipdbid;
    }

    /**
     * @param int $ipdbid
     * @return PinballYMenuEntry
     */
    public function setIpdbid(int $ipdbid): PinballYMenuEntry
    {
        $this->ipdbid = $ipdbid;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return PinballYMenuEntry
     */
    public function setDescription(string $description): PinballYMenuEntry
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PinballYMenuEntry
     */
    public function setType(string $type): PinballYMenuEntry
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    /**
     * @param string $manufacturer
     * @return PinballYMenuEntry
     */
    public function setManufacturer(string $manufacturer): PinballYMenuEntry
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @param string $year
     * @return PinballYMenuEntry
     */
    public function setYear(string $year): PinballYMenuEntry
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return string
     */
    public function getRating(): string
    {
        return $this->rating;
    }

    /**
     * @param string $rating
     * @return PinballYMenuEntry
     */
    public function setRating(string $rating): PinballYMenuEntry
    {
        $this->rating = $rating;
        return $this;
    }

    /**
     * @return string
     */
    public function getRom(): string
    {
        return $this->rom;
    }

    /**
     * @param string $rom
     * @return PinballYMenuEntry
     */
    public function setRom(string $rom): PinballYMenuEntry
    {
        $this->rom = $rom;
        return $this;
    }
}
