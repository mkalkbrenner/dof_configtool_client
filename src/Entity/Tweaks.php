<?php

namespace App\Entity;

use App\Component\Utility;

class Tweaks
{
    /**
     * @var string
     */
    private $daySettings = '';

    /**
     * @var string
     */
    private $nightSettings = '';

    /**
     * @var string
     */
    private $synonymSettings = '';

    private $dayIni;

    private $nightIni;

    private $synonymsIni;

    private $directory;

    public function __construct(string $variant = 'day')
    {
        $this->directory = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . DIRECTORY_SEPARATOR . 'tweaks';
        if (!is_dir($this->directory)) {
            mkdir($this->directory);
        }

        $this->dayIni = $this->directory . DIRECTORY_SEPARATOR . 'day.ini';
        $this->nightIni = $this->directory . DIRECTORY_SEPARATOR . 'night.ini';
        $this->synonymsIni = $this->directory . DIRECTORY_SEPARATOR . 'synonyms.ini';
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
        if ($this->daySettings) {
            return Utility::parseIniString($this->daySettings);
        }
        return null;
    }

    public function setDaySettings(?string $settings): self
    {
        $this->daySettings = $settings ?? "\r\n";

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
        if ($this->nightSettings) {
            return Utility::parseIniString($this->nightSettings);
        }
        return null;
    }

    public function setNightSettings(?string $settings): self
    {
        $this->nightSettings = $settings ?? "\r\n";

        return $this;
    }

    public function getSettingsParsed(string $cycle = 'day'): ?array
    {
        return 'day' === $cycle ? $this->getDaySettingsParsed(): $this->getNightSettingsParsed();
    }

    /**
     * @return string
     */
    public function getSynonymsIni(): string
    {
        return $this->synonymsIni;
    }

    public function getSynonymSettings(): ?string
    {
        return $this->synonymSettings;
    }

    public function getSynonymSettingsParsed(): ?array
    {
        return Utility::parseIniString($this->synonymSettings);
    }

    public function setSynonymSettings(?string $synonymSettings): self
    {
        $this->synonymSettings = $synonymSettings ?? "\r\n";

        return $this;
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

        if (file_exists($this->synonymsIni)) {
            $this->synonymSettings = file_get_contents($this->synonymsIni);
        } else {
            $this->synonymSettings = "# Beatles = Seawitch\r\nseawitfp = seawitch\r\n";
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

        if (!file_put_contents($this->synonymsIni, $this->synonymSettings)) {
            throw new \RuntimeException('Could not write file ' . $this->synonymsIni);
        }

        return $this;
    }
}
