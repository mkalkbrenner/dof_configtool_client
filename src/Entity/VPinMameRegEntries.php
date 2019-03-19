<?php

namespace App\Entity;

class VPinMameRegEntries implements \IteratorAggregate
{
    private $entries = [];

    public function getEntries(): ?array
    {
        return $this->entries;
    }

    public function setEntries(array $entries): self
    {
        $this->entries = $entries;

        return $this;
    }

    public function load(): self
    {
        $this->entries = VPinMameRegEntry::loadAll();
        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->entries);
    }
}
