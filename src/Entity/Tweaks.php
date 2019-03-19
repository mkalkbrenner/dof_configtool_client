<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Tweaks
{
    const TWEAKS_INI = __DIR__ . '/../../ini/tweaks.ini';

    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $settings = '';

    public function getSettings(): ?string
    {
        return $this->settings;
    }

    public function getSettingsParsed(): ?array
    {
        return parse_ini_string($this->settings, TRUE);
    }

    public function setSettings(string $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function load() : self
    {
        if (file_exists(self::TWEAKS_INI)) {
            $this->settings = file_get_contents(self::TWEAKS_INI);
        }

        return $this;
    }

    public function persist() : self
    {
        if (!file_put_contents(self::TWEAKS_INI, $this->settings)) {
            throw new \RuntimeException('Could not write file ' . self::TWEAKS_INI);
        }

        return $this;
    }
}
