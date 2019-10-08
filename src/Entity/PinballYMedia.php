<?php

namespace App\Entity;

class PinballYMedia
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $instructionCardImage;

    /**
     * @var string
     */
    private $dmdImage;

    /**
     * @var string
     */
    private $topperImage;

    /**
     * @var string
     */
    private $tableImage;

    /**
     * @var string
     */
    private $backglassImage;

    /**
     * @var string
     */
    private $wheelImage;

    public function __construct(string $table)
    {
        $this->table = str_replace(['/', ':'], [';', ''], $table);
    }

    /**
     * @param string $file
     * @return PinballYMedia
     */
    public function setPath(string $path): self
    {
        $this->path = $path . DIRECTORY_SEPARATOR . 'Media';
        return $this;
    }

    /**
     * @return string
     */
    public function getInstructionCardImage(): ?string
    {
        return $this->instructionCardImage;
    }

    /**
     * @return string
     */
    public function getDmdImage(): ?string
    {
        return $this->dmdImage;
    }

    /**
     * @return string
     */
    public function getTopperImage(): ?string
    {
        return $this->topperImage;
    }

    /**
     * @return string
     */
    public function getTableImage(): ?string
    {
        return $this->tableImage;
    }

    /**
     * @return string
     */
    public function getBackglassImage(): ?string
    {
        return $this->backglassImage;
    }

    /**
     * @return string
     */
    public function getWheelImage(): ?string
    {
        return $this->wheelImage;
    }

    public function load(): self
    {
        foreach ([
            'instructionCard' => $this->path . DIRECTORY_SEPARATOR . 'Instruction Cards' . DIRECTORY_SEPARATOR,
            'backglass' => $this->path . DIRECTORY_SEPARATOR . 'Visual Pinball X' . DIRECTORY_SEPARATOR . 'Backglass Images',
            'dmd' => $this->path . DIRECTORY_SEPARATOR . 'Visual Pinball X' . DIRECTORY_SEPARATOR . 'DMD Images',
            'topper' => $this->path . DIRECTORY_SEPARATOR . 'Visual Pinball X' . DIRECTORY_SEPARATOR . 'Topper Images',
            'table' => $this->path . DIRECTORY_SEPARATOR . 'Visual Pinball X' . DIRECTORY_SEPARATOR . 'Table Images',
            'wheel' => $this->path . DIRECTORY_SEPARATOR . 'Visual Pinball X' . DIRECTORY_SEPARATOR . 'Wheel Images',
        ] as $property => $path) {
            if (is_dir($path)) {
                foreach (scandir($path) as $filename) {
                    if (preg_match('/(.+)\.(jpg|jpeg|png)$/i', $filename, $matches)) {
                        if ($this->table === $matches[1]) {
                            $this->{$property . 'Image'} = $path . DIRECTORY_SEPARATOR . $matches[0];
                            break;
                        }
                    }
                }
            }
        }

        return $this;
    }
}
