<?php

namespace App\Entity;

class PinballYGameStats implements \IteratorAggregate
{
    /**
     * @var PinballYGameStat[]
     */
    private $stats = [];

    /**
     * @var string
     */
    private $file;

    /**
     * @param string $file
     * @return PinballYGameStats
     */
    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function getStats(): ?array
    {
        return $this->stats;
    }

    public function getStat($game_or_table): ?PinballYGameStat
    {
        if (isset($this->stats[$game_or_table])) {
            return $this->stats[$game_or_table];
        }
        foreach (array_keys($this->stats) as $game) {
            if (strpos($game, $game_or_table) === 0) {
                if (($game_or_table . '.' . $this->stats[$game]->getGameGroup()) === $game) {
                    return $this->stats[$game];
                }
            }
        }
        return null;
    }

    public function load(): self
    {
        if ($contents = file_get_contents($this->file)) {
            $contents = iconv('UTF-16', 'UTF-8', $contents);
            // Normalize line endings.
            $contents = preg_replace('/\R/', "\r\n", $contents);
            foreach (explode("\r\n", $contents) as $line) {
                $line = trim($line);
                if ($line) {
                    $stat = new PinballYGameStat($line);
                    $this->stats[$stat->getGame()] = $stat;
                }
            }
        }
        return $this;
    }

    public function persist(): self
    {
        $content = '';
        foreach ($this->stats as $stat) {
            $content .= $stat->getCsv() . "\r\n";
        }

        if (!file_put_contents($this->file, iconv('UTF-8', 'UTF-16', $content))) {
            throw new \RuntimeException('Could not write file ' . $this->file);
        }

        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->stats);
    }
}
