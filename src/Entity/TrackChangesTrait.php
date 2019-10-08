<?php

namespace App\Entity;

trait TrackChangesTrait
{
    private $trackChanges = false;

    private $hasChanges;

    public function trackChanges(bool $track = TRUE): self
    {
        $this->trackChanges = $track;

        return $this;
    }

    public function hasChanges(): bool
    {
        return !empty($this->hasChanges);
    }

    public function getChanges(): ?array
    {
        return $this->hasChanges;
    }
}
