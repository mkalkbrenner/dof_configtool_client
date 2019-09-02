<?php

namespace App\Entity;

class B2STableSettings implements \IteratorAggregate
{
    /**
     * @var B2STableSetting[]
     */
    private $tableSettings = [];

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $contents;

    /**
     * @var string[]
     */
    private $roms;

    /**
     * @param string $file
     * @return B2STableSettings
     */
    public function setPath(string $path): self
    {
        $this->file = $path . DIRECTORY_SEPARATOR . 'B2STableSettings.xml';
        return $this;
    }

    /**
     * @param string $file
     * @return B2STableSettings
     */
    public function setRoms(array $roms): self
    {
        $this->roms = $roms;
        return $this;
    }

    public function getTableSettings(): ?array
    {
        return $this->tableSettings;
    }

    public function getTableSetting(string $rom): ?B2STableSetting
    {
        return $this->tableSettings[$rom] ?? null;
    }

    public function load(?bool $create_new = FALSE): self
    {
        if ($contents = file_get_contents($this->file)) {
            // Normalize line endings.
            $this->contents = preg_replace('/\R/', "\r\n", $contents);
            /** @var B2STableSetting $b2sTableSetting */
            $b2sTableSetting = null;

            /*
             *   <f14_l1>
             *    <HideGrill>2</HideGrill>
             *    <HideB2SDMD>0</HideB2SDMD>
             *    <HideDMD>2</HideDMD>
             *    <LampsSkipFrames>1</LampsSkipFrames>
             *    <SolenoidsSkipFrames>3</SolenoidsSkipFrames>
             *    <GIStringsSkipFrames>3</GIStringsSkipFrames>
             *    <LEDsSkipFrames>0</LEDsSkipFrames>
             *    <UsedLEDType>2</UsedLEDType>
             *    <IsGlowBulbOn>0</IsGlowBulbOn>
             *    <GlowIndex>-1</GlowIndex>
             *    <StartAsEXE>1</StartAsEXE>
             *    <StartBackground>0</StartBackground>
             *    <Animations />
             *    <DualMode>2</DualMode>
             *  </f14_l1>
             */

            foreach (explode("\r\n", $this->contents) as $line) {
                $line = trim($line);
                if ($line) {
                    if (!$b2sTableSetting) {
                        foreach ($this->roms as $rom) {
                            if (strpos($line, '<' . $rom . '>') !== false) {
                                $b2sTableSetting = new B2STableSetting($rom);
                            }
                            elseif ($create_new) {
                                $this->tableSettings[$rom] = new B2STableSetting($rom);
                            }
                        }
                    } else {
                        $rom = $b2sTableSetting->getRom();
                        if (strpos($line, '</' . $rom . '>') !== false) {
                            $b2sTableSetting->trackChanges(true);
                            $this->tableSettings[$rom] = $b2sTableSetting;
                            unset($b2sTableSetting);
                            $b2sTableSetting = null;
                        }
                        elseif (preg_match('/<([^>]+)>(\d+)/', $line, $matches)) {
                            $method = 'set' . $matches[1];
                            if (method_exists($b2sTableSetting, $method)) {
                                $b2sTableSetting->{$method}($matches[2]);
                            }
                            $b2sTableSetting->addOriginalLine($line);
                        }
                    }
                }
            }
        }
        return $this;
    }

    public function persist(): self
    {
        foreach ($this->getTableSettings() as $tableSetting) {
            $rom = preg_quote($tableSetting->getRom(), '@');
            $this->contents = preg_replace('@<' . $rom . '>.*</' . $rom . '>@ms', $tableSetting->toXML(), $this->contents);
        }

        if (!file_put_contents($this->file, $this->contents)) {
            throw new \RuntimeException('Could not write file ' . $this->file);
        }

        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->tableSettings);
    }
}
