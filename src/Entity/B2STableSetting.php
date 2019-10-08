<?php

namespace App\Entity;

class B2STableSetting
{
    use TrackChangesTrait;

    /**
     * @var string
     */
    private $rom;

    /**
     * @var int 0 = Visible
     *          1 = Hidden
     *          2 = Standard
     */
    private $HideGrill = 2;

    /**
     * @var int 0 = Visible
     *          1 = Hidden
     */
    private $HideB2SDMD = 0 ;

    /**
     * @var int 0 = Visible
     *          1 = Hidden
     *          2 = Standard
     */
    private $HideDMD = 2;

    /**
     * @var int
     */
    private $LampsSkipFrames = 1;

    /**
     * @var int
     */
    private $SolenoidsSkipFrames = 3;

    /**
     * @var int
     */
    private $GIStringsSkipFrames = 3;

    /**
     * @var int
     */
    private $LEDsSkipFrames = 0;

    /**
     * @var int
     */
    private $UsedLEDType = 2;

    /**
     * @var int
     */
    private $IsGlowBulbOn = 0;

    /**
     * @var int
     */
    private $GlowIndex = -1;

    /**
     * @var int
     */
    private $StartAsEXE = 1;

    /**
    /**
     * @var int
     */
    private $StartBackground = 0;

    private $originalLines = [];

    public function __construct(string $rom)
    {
        $this->rom = $rom;
    }

    /**
     * @return string
     */
    public function getRom(): string
    {
        return $this->rom;
    }

    /**
     * @param string $rom
     * @return B2STableSetting
     */
    public function setRom(string $rom): B2STableSetting
    {
        $this->rom = $rom;
        return $this;
    }

    /**
     * @return int
     */
    public function getHideGrill(): int
    {
        return $this->HideGrill;
    }

    /**
     * @param int $HideGrill
     * @return B2STableSetting
     */
    public function setHideGrill(int $HideGrill): B2STableSetting
    {
        if ($this->trackChanges && $this->HideGrill != $HideGrill) {
            $this->hasChanges['HideGrill'] = TRUE;
        }
        $this->HideGrill = $HideGrill;
        return $this;
    }

    /**
     * @return int
     */
    public function getHideB2SDMD(): int
    {
        return $this->HideB2SDMD;
    }

    /**
     * @param int $HideB2SDMD
     * @return B2STableSetting
     */
    public function setHideB2SDMD(int $HideB2SDMD): B2STableSetting
    {
        if ($this->trackChanges && $this->HideB2SDMD != $HideB2SDMD) {
            $this->hasChanges['HideB2SDMD'] = TRUE;
        }
        $this->HideB2SDMD = $HideB2SDMD;
        return $this;
    }

    /**
     * @return int
     */
    public function getHideDMD(): int
    {
        return $this->HideDMD;
    }

    /**
     * @param int $HideDMD
     * @return B2STableSetting
     */
    public function setHideDMD(int $HideDMD): B2STableSetting
    {
        if ($this->trackChanges && $this->HideDMD != $HideDMD) {
            $this->hasChanges['HideDMD'] = TRUE;
        }
        $this->HideDMD = $HideDMD;
        return $this;
    }

    /**
     * @return int
     */
    public function getLampsSkipFrames(): int
    {
        return $this->LampsSkipFrames;
    }

    /**
     * @param int $LampsSkipFrames
     * @return B2STableSetting
     */
    public function setLampsSkipFrames(int $LampsSkipFrames): B2STableSetting
    {
        if ($this->trackChanges && $this->LampsSkipFrames != $LampsSkipFrames) {
            $this->hasChanges['LampsSkipFrames'] = TRUE;
        }
        $this->LampsSkipFrames = $LampsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getSolenoidsSkipFrames(): int
    {
        return $this->SolenoidsSkipFrames;
    }

    /**
     * @param int $SolenoidsSkipFrames
     * @return B2STableSetting
     */
    public function setSolenoidsSkipFrames(int $SolenoidsSkipFrames): B2STableSetting
    {
        if ($this->trackChanges && $this->SolenoidsSkipFrames != $SolenoidsSkipFrames) {
            $this->hasChanges['SolenoidsSkipFrames'] = TRUE;
        }
        $this->SolenoidsSkipFrames = $SolenoidsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getGIStringsSkipFrames(): int
    {
        return $this->GIStringsSkipFrames;
    }

    /**
     * @param int $GIStringsSkipFrames
     * @return B2STableSetting
     */
    public function setGIStringsSkipFrames(int $GIStringsSkipFrames): B2STableSetting
    {
        if ($this->trackChanges && $this->GIStringsSkipFrames != $GIStringsSkipFrames) {
            $this->hasChanges['GIStringsSkipFrames'] = TRUE;
        }
        $this->GIStringsSkipFrames = $GIStringsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getLEDsSkipFrames(): int
    {
        return $this->LEDsSkipFrames;
    }

    /**
     * @param int $LEDsSkipFrames
     * @return B2STableSetting
     */
    public function setLEDsSkipFrames(int $LEDsSkipFrames): B2STableSetting
    {
        if ($this->trackChanges && $this->LEDsSkipFrames != $LEDsSkipFrames) {
            $this->hasChanges['LEDsSkipFrames'] = TRUE;
        }
        $this->LEDsSkipFrames = $LEDsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getUsedLEDType(): int
    {
        return $this->UsedLEDType;
    }

    /**
     * @param int $UsedLEDType
     * @return B2STableSetting
     */
    public function setUsedLEDType(int $UsedLEDType): B2STableSetting
    {
        if ($this->trackChanges && $this->UsedLEDType != $UsedLEDType) {
            $this->hasChanges['UsedLEDType'] = TRUE;
        }
        $this->UsedLEDType = $UsedLEDType;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsGlowBulbOn(): int
    {
        return $this->IsGlowBulbOn;
    }

    /**
     * @param int $IsGlowBulbOn
     * @return B2STableSetting
     */
    public function setIsGlowBulbOn(int $IsGlowBulbOn): B2STableSetting
    {
        if ($this->trackChanges && $this->IsGlowBulbOn != $IsGlowBulbOn) {
            $this->hasChanges['IsGlowBulbOn'] = TRUE;
        }
        $this->IsGlowBulbOn = $IsGlowBulbOn;
        return $this;
    }

    /**
     * @return int
     */
    public function getGlowIndex(): int
    {
        return $this->GlowIndex;
    }

    /**
     * @param int $GlowIndex
     * @return B2STableSetting
     */
    public function setGlowIndex(int $GlowIndex): B2STableSetting
    {
        if ($this->trackChanges && $this->GlowIndex != $GlowIndex) {
            $this->hasChanges['GlowIndex'] = TRUE;
        }
        $this->GlowIndex = $GlowIndex;
        return $this;
    }

    /**
     * @return bool
     */
    public function getStartAsEXE(): bool
    {
        return (bool) $this->StartAsEXE;
    }

    /**
     * @param int $StartAsEXE
     * @return B2STableSetting
     */
    public function setStartAsEXE(int $StartAsEXE): B2STableSetting
    {
        if ($this->trackChanges && $this->StartAsEXE != $StartAsEXE) {
            $this->hasChanges['StartAsEXE'] = TRUE;
        }
        $this->StartAsEXE = $StartAsEXE;
        return $this;
    }

    /**
     * @return int
     */
    public function getStartBackground(): bool
    {
        return (bool) $this->StartBackground;
    }

    /**
     * @param int $StartBackground
     * @return B2STableSetting
     */
    public function setStartBackground(int $StartBackground): B2STableSetting
    {
        if ($this->trackChanges && $this->StartBackground != $StartBackground) {
            $this->hasChanges['StartBackground'] = TRUE;
        }
        $this->StartBackground = $StartBackground;
        return $this;
    }

    public function addOriginalLine(string $line): self
    {
        $this->originalLines[] = $line;
        return $this;
    }

    public function toXML() {
        $xml = '  <' . $this->rom . ">\r\n";

        if (!$this->originalLines) {
            foreach (get_class_methods($this) as $getter) {
                if (strpos($getter, 'get') === 0 && 'getRom' !== $getter && 'getChanges' !== $getter) {
                    $property = preg_replace('/^get/', '', $getter);
                    $xml .= '    <' . $property . '>' . ((int) $this->{$getter}()) . '</' . $property . ">\r\n";
                }
            }
            $xml .= '    <Animations />';
        } else {
            foreach ($this->originalLines as $line) {
                if (preg_match('/<([^>]+)>(\d+)/', $line, $matches)) {
                    if (!empty($this->hasChanges[$matches[1]])) {
                        $line = '<' . $matches[1] . '>' . ((int) $this->{$matches[1]}) . '</' . $matches[1] . '>';
                    }
                }
                $xml .= '    ' . $line . "\r\n";
            }
        }

        return $xml .= "\r\n  </" . $this->rom . '>';
    }
}
