<?php

namespace App\Entity;

use Windows\Registry\Registry;
use Windows\Registry\RegistryKey;

class VPinMameRegEntry
{
    private $rom;

    private $cabinet_mode = NULL;

    private $ignore_rom_crc = NULL;

    private $ddraw = NULL;

    private $trackChanges = FALSE;

    private $hasChanges = FALSE;

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
        else {
            // Useful for development on unix-like systems.
            return new RegistryKeyDummy();
        }
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
            $this->hasChanges = TRUE;
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
            $this->hasChanges = TRUE;
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
            $this->hasChanges = TRUE;
        }
        $this->ddraw = $ddraw;

        return $this;
    }

    public function trackChanges(bool $track = TRUE): self
    {
        $this->trackChanges = $track;

        return $this;
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
            if (!is_null($this->ignore_rom_crc)) {
                $key->setValue('ignore_rom_crc', $this->ignore_rom_crc, RegistryKey::TYPE_DWORD);
            }
            if (!is_null($this->cabinet_mode)) {
                $key->setValue('cabinet_mode', $this->cabinet_mode, RegistryKey::TYPE_DWORD);
            }
            if (!is_null($this->ddraw)) {
                $key->setValue('ddraw', $this->ddraw, RegistryKey::TYPE_DWORD);
            }
            $this->hasChanges = FALSE;
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
                    $entry->setCabinetMode((bool)$value);
                    break;
                case 'ignore_rom_crc':
                    $entry->setIgnoreRomCrc((bool)$value);
                    break;
                case 'ddraw':
                    $entry->setDdraw((bool)$value);
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
            'ddraw' => (bool)random_int(0, 1),
            'dummy' => (bool)random_int(0, 1),
        ];
    }

    public function setValue($name, $value, $type)
    {
        // nop
    }
}