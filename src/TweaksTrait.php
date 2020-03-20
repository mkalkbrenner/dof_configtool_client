<?php

namespace App;

use App\Entity\Tweaks;

trait TweaksTrait
{
    /** @var Tweaks */
    protected $tweaks;

    /**
     * @return Tweaks
     */
    public function getTweaks(): Tweaks
    {
        if (!$this->tweaks) {
            $this->tweaks = new Tweaks();
            $this->tweaks->load();
        }
        return $this->tweaks;
    }
}