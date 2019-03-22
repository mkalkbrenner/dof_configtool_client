<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Tweaks
{
    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $settings = '';

    private $ini;

    public function __construct()
    {
        $this->ini = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini/')) . 'tweaks.ini';
    }

    /**
     * @return string
     */
    public function getIni(): string
    {
        return $this->ini;
    }

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
        if (file_exists($this->ini)) {
            $this->settings = file_get_contents($this->ini);
        }

        return $this;
    }

    public function persist() : self
    {
        if (!file_put_contents($this->ini, $this->settings)) {
            throw new \RuntimeException('Could not write file ' . $this->ini);
        }

        return $this;
    }
}
