<?php

namespace App\Entity;

class B2STableSetting
{
    /**
     * @var string
     */
    private $rom;

    /**
     * @var int
     */
    private $HideGrill;

    /**
     * @var int
     */
    private $HideB2SDMD;

    /**
     * @var int
     */
    private $HideDMD;

    /**
     * @var int
     */
    private $LampsSkipFrames;

    /**
     * @var int
     */
    private $SolenoidsSkipFrames;

    /**
     * @var int
     */
    private $GIStringsSkipFrames;

    /**
     * @var int
     */
    private $LEDsSkipFrames;

    /**
     * @var int
     */
    private $UsedLEDType;

    /**
     * @var int
     */
    private $IsGlowBulbOn;

    /**
     * @var int
     */
    private $GlowIndex;

    /**
     * @var int
     */
    private $StartAsEXE;

    /**
    /**
     * @var int
     */
    private $StartBackground;

    /**
     * @var int
     */
    private $DualMode;

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
    public function getHideGrill(): ?int
    {
        return $this->HideGrill;
    }

    /**
     * @param int $HideGrill
     * @return B2STableSetting
     */
    public function setHideGrill(int $HideGrill): B2STableSetting
    {
        $this->HideGrill = $HideGrill;
        return $this;
    }

    /**
     * @return int
     */
    public function getHideB2SDMD(): ?int
    {
        return $this->HideB2SDMD;
    }

    /**
     * @param int $HideB2SDMD
     * @return B2STableSetting
     */
    public function setHideB2SDMD(int $HideB2SDMD): B2STableSetting
    {
        $this->HideB2SDMD = $HideB2SDMD;
        return $this;
    }

    /**
     * @return int
     */
    public function getHideDMD(): ?int
    {
        return $this->HideDMD;
    }

    /**
     * @param int $HideDMD
     * @return B2STableSetting
     */
    public function setHideDMD(int $HideDMD): B2STableSetting
    {
        $this->HideDMD = $HideDMD;
        return $this;
    }

    /**
     * @return int
     */
    public function getLampsSkipFrames(): ?int
    {
        return $this->LampsSkipFrames;
    }

    /**
     * @param int $LampsSkipFrames
     * @return B2STableSetting
     */
    public function setLampsSkipFrames(int $LampsSkipFrames): B2STableSetting
    {
        $this->LampsSkipFrames = $LampsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getSolenoidsSkipFrames(): ?int
    {
        return $this->SolenoidsSkipFrames;
    }

    /**
     * @param int $SolenoidsSkipFrames
     * @return B2STableSetting
     */
    public function setSolenoidsSkipFrames(int $SolenoidsSkipFrames): B2STableSetting
    {
        $this->SolenoidsSkipFrames = $SolenoidsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getGIStringsSkipFrames(): ?int
    {
        return $this->GIStringsSkipFrames;
    }

    /**
     * @param int $GIStringsSkipFrames
     * @return B2STableSetting
     */
    public function setGIStringsSkipFrames(int $GIStringsSkipFrames): B2STableSetting
    {
        $this->GIStringsSkipFrames = $GIStringsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getLEDsSkipFrames(): ?int
    {
        return $this->LEDsSkipFrames;
    }

    /**
     * @param int $LEDsSkipFrames
     * @return B2STableSetting
     */
    public function setLEDsSkipFrames(int $LEDsSkipFrames): B2STableSetting
    {
        $this->LEDsSkipFrames = $LEDsSkipFrames;
        return $this;
    }

    /**
     * @return int
     */
    public function getUsedLEDType(): ?int
    {
        return $this->UsedLEDType;
    }

    /**
     * @param int $UsedLEDType
     * @return B2STableSetting
     */
    public function setUsedLEDType(int $UsedLEDType): B2STableSetting
    {
        $this->UsedLEDType = $UsedLEDType;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsGlowBulbOn(): ?int
    {
        return $this->IsGlowBulbOn;
    }

    /**
     * @param int $IsGlowBulbOn
     * @return B2STableSetting
     */
    public function setIsGlowBulbOn(int $IsGlowBulbOn): B2STableSetting
    {
        $this->IsGlowBulbOn = $IsGlowBulbOn;
        return $this;
    }

    /**
     * @return int
     */
    public function getGlowIndex(): ?int
    {
        return $this->GlowIndex;
    }

    /**
     * @param int $GlowIndex
     * @return B2STableSetting
     */
    public function setGlowIndex(int $GlowIndex): B2STableSetting
    {
        $this->GlowIndex = $GlowIndex;
        return $this;
    }

    /**
     * @return int
     */
    public function getStartAsEXE(): ?int
    {
        return $this->StartAsEXE;
    }

    /**
     * @param int $StartAsEXE
     * @return B2STableSetting
     */
    public function setStartAsEXE(int $StartAsEXE): B2STableSetting
    {
        $this->StartAsEXE = $StartAsEXE;
        return $this;
    }

    /**
     * @return int
     */
    public function getStartBackground(): ?int
    {
        return $this->StartBackground;
    }

    /**
     * @param int $StartBackground
     * @return B2STableSetting
     */
    public function setStartBackground(int $StartBackground): B2STableSetting
    {
        $this->StartBackground = $StartBackground;
        return $this;
    }

    /**
     * @return int
     */
    public function getDualMode(): ?int
    {
        return $this->DualMode;
    }

    /**
     * @param int $DualMode
     * @return B2STableSetting
     */
    public function setDualMode(int $DualMode): B2STableSetting
    {
        $this->DualMode = $DualMode;
        return $this;
    }

    public function toXML() {
        $xml = '<' . $this->rom . ">\r\n";
        foreach (get_class_methods($this) as $getter) {
            if (strpos($getter, 'get') === 0 && 'getRom' !== $getter) {
                $property = preg_replace('/^get/', '', $getter);
                $xml .= '    <' . $property . '>' . $this->{$getter}() . '</' . $property . ">\r\n";
            }
        }
        return $xml . "    <Animations />\r\n  </" . $this->rom . '>';
    }
}
