<?php

namespace App\Entity;

use Windows\Registry\Registry;
use Windows\Registry\RegistryKey;

class VPinMameRegEntry
{
    private $rom;

    private $cabinet_mode;

    private $ignore_rom_crc;

    private $ddraw;

    private $sound;

    private $sound_mode;

    private $samples;

    private $dmd_colorize;

    private $showpindmd;

    private $showwindmd;

    private $synclevel;

    private $trackChanges = FALSE;

    private $hasChanges;

    private $hkcu;

    public function __construct($hkcu = NULL)
    {
        $this->hkcu = $hkcu ?: self::getCurentUserKey();
    }

    public static function getCurentUserKey()
    {
        if (extension_loaded('com_dotnet')) {
            return Registry::connect()->getCurrentUser();
        }

        // Useful for development on unix-like systems.
        return new RegistryKeyDummy();
    }

    public function getRom(): ?string
    {
        return $this->rom;
    }

    public function setRom(string $rom): self
    {
        $this->rom = $rom;

        return $this;
    }

    public function getCabinetMode(): ?bool
    {
        return $this->cabinet_mode;
    }

    public function setCabinetMode(bool $cabinet_mode): self
    {
        if ($this->trackChanges && $this->cabinet_mode != $cabinet_mode) {
            $this->hasChanges['CabinetMode'] = TRUE;
        }
        $this->cabinet_mode = $cabinet_mode;

        return $this;
    }

    public function getIgnoreRomCrc(): ?bool
    {
        return $this->ignore_rom_crc;
    }

    public function setIgnoreRomCrc(bool $ignore_rom_crc): self
    {
        if ($this->trackChanges && $this->ignore_rom_crc != $ignore_rom_crc) {
            $this->hasChanges['IgnoreRomCrc'] = TRUE;
        }
        $this->ignore_rom_crc = $ignore_rom_crc;

        return $this;
    }

    public function getDdraw(): ?bool
    {
        return $this->ddraw;
    }

    public function setDdraw(bool $ddraw): self
    {
        if ($this->trackChanges && $this->ddraw != $ddraw) {
            $this->hasChanges['Ddraw'] = TRUE;
        }
        $this->ddraw = $ddraw;

        return $this;
    }

    public function getSound(): ?bool
    {
        return $this->sound;
    }

    public function setSound(bool $sound): self
    {
        if ($this->trackChanges && $this->sound != $sound) {
            $this->hasChanges['Sound'] = TRUE;
        }
        $this->sound = $sound;

        return $this;
    }

    public function getSoundMode(): ?int
    {
        return $this->sound_mode;
    }

    public function setSoundMode(?int $sound_mode): self
    {
        $sound_mode = (int) $sound_mode;
        if ($this->trackChanges && $this->sound_mode != $sound_mode) {
            $this->hasChanges['SoundMode'] = TRUE;
        }
        $this->sound_mode = $sound_mode;

        return $this;
    }

    public function getSamples(): ?bool
    {
        return $this->samples;
    }

    public function setSamples(bool $samples): self
    {
        if ($this->trackChanges && $this->samples != $samples) {
            $this->hasChanges['Samples'] = TRUE;
        }
        $this->samples = $samples;

        return $this;
    }

    public function getDmdColorize(): ?bool
    {
        return $this->dmd_colorize;
    }

    public function setDmdColorize(bool $dmd_colorize): self
    {
        if ($this->trackChanges && $this->dmd_colorize != $dmd_colorize) {
            $this->hasChanges['DmdColorize'] = TRUE;
        }
        $this->dmd_colorize = $dmd_colorize;

        return $this;
    }

    public function getShowpindmd(): ?bool
    {
        return $this->showpindmd;
    }

    public function setShowpindmd(bool $showpindmd): self
    {
        if ($this->trackChanges && $this->showpindmd != $showpindmd) {
            $this->hasChanges['Showpindmd'] = TRUE;
        }
        $this->showpindmd = $showpindmd;

        return $this;
    }

    public function getShowwindmd(): ?bool
    {
        return $this->showwindmd;
    }

    public function setShowwindmd(bool $showwindmd): self
    {
        if ($this->trackChanges && $this->showwindmd != $showwindmd) {
            $this->hasChanges['Showwindmd'] = TRUE;
        }
        $this->showwindmd = $showwindmd;

        return $this;
    }

    public function getSynclevel(): ?int
    {
        return $this->synclevel;
    }

    public function setSynclevel(int $synclevel): self
    {
        if ($this->trackChanges && $this->synclevel != $synclevel) {
            $this->hasChanges['Synclevel'] = TRUE;
        }
        $this->synclevel = $synclevel;

        return $this;
    }

    public function trackChanges(bool $track = TRUE): self
    {
        $this->trackChanges = $track;

        return $this;
    }

    public function getChanges(): ?array
    {
        return $this->hasChanges;
    }

    public function load(): self
    {
        self::readValues(
            $this,
            $this->hkcu->getSubKey("Software\\Freeware\\Visual PinMame\\" . $this->rom)
        );

        return $this;
    }

    public function persist(): self
    {
        if (!$this->trackChanges || $this->hasChanges) {
            $key = $this->hkcu->getSubKey("Software\\Freeware\\Visual PinMame\\" . $this->rom);
            if (null !== $this->ignore_rom_crc) {
                $key->setValue('ignore_rom_crc', $this->ignore_rom_crc, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->cabinet_mode) {
                $key->setValue('cabinet_mode', $this->cabinet_mode, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->sound) {
                $key->setValue('sound', $this->sound, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->sound_mode) {
                $key->setValue('sound_mode', $this->sound_mode, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->samples) {
                $key->setValue('samples', $this->samples, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->ddraw) {
                $key->setValue('ddraw', $this->ddraw, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->dmd_colorize) {
                $key->setValue('dmd_colorize', $this->dmd_colorize, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->showpindmd) {
                $key->setValue('showpindmd', $this->showpindmd, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->showwindmd) {
                $key->setValue('showwindmd', $this->showwindmd, RegistryKey::TYPE_DWORD);
            }
            if (null !== $this->synclevel) {
                $key->setValue('synclevel', $this->synclevel, RegistryKey::TYPE_DWORD);
            }
            $this->hasChanges = null;
        }

        return $this;
    }

    public static function loadAll(): array
    {
        $hkcu = self::getCurentUserKey();
        $key = $hkcu->getSubKey("Software\\Freeware\\Visual PinMame");

        $entries = [];
        foreach ($key->getSubKeyIterator() as $rom => $subKey) {
            $entries[$rom] = new VPinMameRegEntry($hkcu);
            $entries[$rom]->setRom($rom);
            self::readValues($entries[$rom], $subKey);
        }

        return $entries;
    }

    protected static function readValues(VPinMameRegEntry $entry, $key) {
        /** @var RegistryKey $key */
        foreach ($key->getValueIterator() as $valueName => $value) {
            switch ($valueName) {
                case 'cabinet_mode':
                    $entry->setCabinetMode((bool) $value);
                    break;
                case 'ignore_rom_crc':
                    $entry->setIgnoreRomCrc((bool) $value);
                    break;
                case 'sound':
                    $entry->setSound((bool) $value);
                    break;
                case 'sound_mode':
                    $entry->setSoundMode((int) $value);
                    break;
                case 'samples':
                    $entry->setSamples((bool) $value);
                    break;
                case 'ddraw':
                    $entry->setDdraw((bool) $value);
                    break;
                case 'dmd_colorize':
                    $entry->setDmdColorize((bool) $value);
                    break;
                case 'showpindmd':
                    $entry->setShowpindmd((bool) $value);
                    break;
                case 'showwindmd':
                    $entry->setShowwindmd((bool) $value);
                    break;
                case 'synclevel':
                    $entry->setSynclevel((int) $value);
                    break;
            }
        }
        $entry->trackChanges();
    }
}

class RegistryKeyDummy
{
    public function getSubKey($name)
    {
        return $this;
    }

    public function getSubKeyIterator()
    {
        return [
            'aar' => $this,
            'default' => $this,
            'aavenger' => $this,
            'ACDC' => $this,
            'babypac' => $this,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getValueIterator()
    {
        return [
            'cabinet_mode' => (bool)random_int(0, 1),
            'ignore_rom_crc' => (bool)random_int(0, 1),
            'sound' => (bool)random_int(0, 1),
            'sound_mode' => random_int(0, 3),
            'samples' => (bool)random_int(0, 1),
            'ddraw' => (bool)random_int(0, 1),
            'dmd_colorize' => (bool)random_int(0, 1),
            'showpindmd' => (bool)random_int(0, 1),
            'showwindmd' => (bool)random_int(0, 1),
            'synclevel' => random_int(0, 600),
        ];
    }

    public function setValue($name, $value, $type)
    {
        // nop
    }
}