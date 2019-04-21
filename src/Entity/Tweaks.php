<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Tweaks
{
    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $daySettings = '';

    /**
     * @var string
     */
    private $nightSettings = '';

    private $dayIni;

    private $nightIni;

    private $directory;

    public function __construct(string $variant = 'day')
    {
        $this->directory = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . DIRECTORY_SEPARATOR . 'tweaks';
        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }

        $this->dayIni = $this->directory . DIRECTORY_SEPARATOR . 'day.ini';
        $this->nightIni = $this->directory . DIRECTORY_SEPARATOR . 'night.ini';
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getDayIni(): string
    {
        return $this->dayIni;
    }

    public function getDaySettings(): ?string
    {
        return $this->daySettings;
    }

    public function getDaySettingsParsed(): ?array
    {
        return parse_ini_string($this->daySettings, TRUE);
    }

    public function setDaySettings(string $settings): self
    {
        $this->daySettings = $settings;

        return $this;
    }

    /**
     * @return string
     */
    public function getNightIni(): string
    {
        return $this->nightIni;
    }

    public function getNightSettings(): ?string
    {
        return $this->nightSettings;
    }

    public function getNightSettingsParsed(): ?array
    {
        return parse_ini_string($this->nightSettings, TRUE);
    }

    public function setNightSettings(string $settings): self
    {
        $this->nightSettings = $settings;

        return $this;
    }

    public function getSettingsParsed(string $cycle = 'day'): ?array
    {
        return 'day' == $cycle ? $this->getDaySettingsParsed(): $this->getNightSettingsParsed();
    }

    public function load(): self
    {
        if (file_exists($this->dayIni)) {
            $this->daySettings = file_get_contents($this->dayIni);
        } else {
            // 0.2.x backward compatibility
            $old = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . DIRECTORY_SEPARATOR . 'tweaks.ini';
            if (file_exists($old)) {
                $this->daySettings = file_get_contents($old);
            }
        }

        if (file_exists($this->nightIni)) {
            $this->nightSettings = file_get_contents($this->nightIni);
        }

        return $this;
    }

    public function persist(): self
    {
        if (!file_put_contents($this->dayIni, $this->daySettings)) {
            throw new \RuntimeException('Could not write file ' . $this->dayIni);
        }

        if (!file_put_contents($this->nightIni, $this->nightSettings)) {
            throw new \RuntimeException('Could not write file ' . $this->nightIni);
        }

        return $this;
    }
}
