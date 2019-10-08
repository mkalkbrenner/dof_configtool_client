<?php

namespace App\Entity;

class PinballYMenu implements \IteratorAggregate
{
    /**
     * @var PinballYMenuEntry[]
     */
    private $menuEntries = [];

    /**
     * @var string
     */
    private $file;

    /**
     * @param string $file
     * @return PinballYMenu
     */
    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getMenuEntries(): ?array
    {
        return $this->menuEntries;
    }

    public function getMenuEntry(string $table): ?PinballYMenuEntry
    {
        return $this->menuEntries[$table] ?? null;
    }

    public function load(): self
    {
        if ($contents = file_get_contents($this->file)) {
            $xml = simplexml_load_string('<?xml version=\'1.0\'?>' . iconv('Windows-1251', 'UTF-8', $contents));
            foreach ($xml->game as $game) {
                foreach ($game->attributes() as $key => $value) {
                    if ('name' === $key) {
                        $this->menuEntries[(string) $value] = new PinballYMenuEntry((string) $value, $game);
                        break;
                    }
                }
            }
        }
        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->menuEntries);
    }
}
