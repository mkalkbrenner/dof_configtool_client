<?php

namespace App\Entity;

class Toy
{
    private $fileName;

    private $name;

    private $number;

    private $ports;

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPorts(): ?array
    {
        return $this->ports;
    }

    public function setPorts(array $ports): self
    {
        $this->ports = $ports;

        return $this;
    }
}