<?php

namespace App\Entity;

class VPinMameRegEntries implements \IteratorAggregate
{
    private $entries = [];
    private $tableMapping = [];

    public function getEntries(): ?array
    {
        return $this->entries;
    }

    public function getEntry($key): ?VPinMameRegEntry
    {
        return $this->entries[$key] ?? null;
    }

    public function addEntry(VPinMameRegEntry $entry): self
    {
        $this->entries[$entry->getRom()] = $entry;

        return $this;
    }

    public function setEntries(array $entries): self
    {
        // 'default' should be at the end of the list.
        if (isset($entries['default'])) {
            $default = $entries['default'];
            unset($entries['default']);
        }
        $this->entries = $entries + ['default' => $default];

        return $this;
    }

    public function setTableMapping(array $tableMapping): self
    {
        $this->tableMapping = $tableMapping;

        return $this;
    }

    public function load(): self
    {
        $this->setEntries(VPinMameRegEntry::loadAll($this->tableMapping));
        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->entries);
    }
}
