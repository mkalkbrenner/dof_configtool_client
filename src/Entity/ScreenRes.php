<?php

namespace App\Entity;

class ScreenRes
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $playfieldWidth = 0;

    /**
     * @var int
     */
    private $playfieldHeight = 0;

    /**
     * @var int
     */
    private $backglassWidth = 0;

    /**
     * @var int
     */
    private $backglassHeight = 0;

    /**
     * @var int
     */
    private $backglassDisplay = 0;

    /**
     * @var int
     */
    private $backglassXOffsetRelativeToPlayfieldLeft = 0;

    /**
     * @var int
     */
    private $backglassYOffsetRelativeToPlayfieldTop = 0;

    /**
     * @var int
     */
    private $dmdWidth = 0;

    /**
     * @var int
     */
    private $dmdHeight = 0;

    /**
     * @var int
     */
    private $dmdXOffsetRelativeToBackglassLeft = 0;

    /**
     * @var int
     */
    private $dmdYOffsetRelativeToBackglassTop = 0;

    /**
     * @param string $file
     * @return ScreenRes
     */
    public function setPath(string $path): self
    {
        $this->file = $path . DIRECTORY_SEPARATOR . 'ScreenRes.txt';
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayfieldWidth(): int
    {
        return $this->playfieldWidth;
    }

    /**
     * @param int $playfieldWidth
     * @return ScreenRes
     */
    public function setPlayfieldWidth(int $playfieldWidth): ScreenRes
    {
        $this->playfieldWidth = $playfieldWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlayfieldHeight(): int
    {
        return $this->playfieldHeight;
    }

    /**
     * @param int $playfieldHeight
     * @return ScreenRes
     */
    public function setPlayfieldHeight(int $playfieldHeight): ScreenRes
    {
        $this->playfieldHeight = $playfieldHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getBackglassWidth(): int
    {
        return $this->backglassWidth;
    }

    /**
     * @param int $backglassWidth
     * @return ScreenRes
     */
    public function setBackglassWidth(int $backglassWidth): ScreenRes
    {
        $this->backglassWidth = $backglassWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getBackglassHeight(): int
    {
        return $this->backglassHeight;
    }

    /**
     * @param int $backglassHeight
     * @return ScreenRes
     */
    public function setBackglassHeight(int $backglassHeight): ScreenRes
    {
        $this->backglassHeight = $backglassHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getBackglassDisplay(): int
    {
        return $this->backglassDisplay;
    }

    /**
     * @param int $backglassDisplay
     * @return ScreenRes
     */
    public function setBackglassDisplay(int $backglassDisplay): ScreenRes
    {
        $this->backglassDisplay = $backglassDisplay;
        return $this;
    }

    /**
     * @return int
     */
    public function getBackglassXOffsetRelativeToPlayfieldLeft(): int
    {
        return $this->backglassXOffsetRelativeToPlayfieldLeft;
    }

    /**
     * @param int $backglassXOffsetRelativeToPlayfieldLeft
     * @return ScreenRes
     */
    public function setBackglassXOffsetRelativeToPlayfieldLeft(int $backglassXOffsetRelativeToPlayfieldLeft): ScreenRes
    {
        $this->backglassXOffsetRelativeToPlayfieldLeft = $backglassXOffsetRelativeToPlayfieldLeft;
        return $this;
    }

    /**
     * @return int
     */
    public function getBackglassYOffsetRelativeToPlayfieldTop(): int
    {
        return $this->backglassYOffsetRelativeToPlayfieldTop;
    }

    /**
     * @param int $backglassYOffsetRelativeToPlayfieldTop
     * @return ScreenRes
     */
    public function setBackglassYOffsetRelativeToPlayfieldTop(int $backglassYOffsetRelativeToPlayfieldTop): ScreenRes
    {
        $this->backglassYOffsetRelativeToPlayfieldTop = $backglassYOffsetRelativeToPlayfieldTop;
        return $this;
    }

    /**
     * @return int
     */
    public function getDmdWidth(): int
    {
        return $this->dmdWidth;
    }

    /**
     * @param int $dmdWidth
     * @return ScreenRes
     */
    public function setDmdWidth(int $dmdWidth): ScreenRes
    {
        $this->dmdWidth = $dmdWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getDmdHeight(): int
    {
        return $this->dmdHeight;
    }

    /**
     * @param int $dmdHeight
     * @return ScreenRes
     */
    public function setDmdHeight(int $dmdHeight): ScreenRes
    {
        $this->dmdHeight = $dmdHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getDmdXOffsetRelativeToBackglassLeft(): int
    {
        return $this->dmdXOffsetRelativeToBackglassLeft;
    }

    /**
     * @return int
     */
    public function getDmdXOffsetRelativeToPlayfieldLeft(): int
    {
        return $this->backglassXOffsetRelativeToPlayfieldLeft + $this->dmdXOffsetRelativeToBackglassLeft;
    }

    /**
     * @param int $dmdXOffsetRelativeToBackglassLeft
     * @return ScreenRes
     */
    public function setDmdXOffsetRelativeToBackglassLeft(int $dmdXOffsetRelativeToBackglassLeft): ScreenRes
    {
        $this->dmdXOffsetRelativeToBackglassLeft = $dmdXOffsetRelativeToBackglassLeft;
        return $this;
    }

    /**
     * @return int
     */
    public function getDmdYOffsetRelativeToBackglassTop(): int
    {
        return $this->dmdYOffsetRelativeToBackglassTop;
    }

    /**
     * @return int
     */
    public function getDmdYOffsetRelativeToPlayfieldTop(): int
    {
        return $this->backglassYOffsetRelativeToPlayfieldTop + $this->dmdYOffsetRelativeToBackglassTop;
    }

    /**
     * @param int $dmdYOffsetRelativeToBackglassTop
     * @return ScreenRes
     */
    public function setDmdYOffsetRelativeToBackglassTop(int $dmdYOffsetRelativeToBackglassTop): ScreenRes
    {
        $this->dmdYOffsetRelativeToBackglassTop = $dmdYOffsetRelativeToBackglassTop;
        return $this;
    }

    public function hasDMD() {
        return $this->dmdHeight && $this->dmdWidth;
    }

    public function mightDMDBeVisible() {
        return
            (($this->playfieldWidth + $this->backglassWidth + $this->dmdWidth) <= ($this->getDmdXOffsetRelativeToPlayfieldLeft() + $this->dmdWidth)) &&
            ($this->getDmdYOffsetRelativeToPlayfieldTop() === 0);
    }

    public function load(): self
    {
        if ($contents = file_get_contents($this->file)) {
            // Normalize line endings.
            $contents = preg_replace('/\R/', "\r\n", $contents);

            $setters = [
                'setPlayfieldWidth',
                'setPlayfieldHeight',
                'setBackglassWidth',
                'setBackglassHeight',
                'setBackglassDisplay',
                'setBackglassXOffsetRelativeToPlayfieldLeft',
                'setBackglassYOffsetRelativeToPlayfieldTop',
                'setDmdWidth',
                'setDmdHeight',
                'setDmdXOffsetRelativeToBackglassLeft',
                'setDmdYOffsetRelativeToBackglassTop',
            ];
            foreach (explode("\r\n", $contents) as $line) {
                $line = trim($line);
                if ($line) {
                    $setter = array_shift($setters);
                    $this->{$setter}((int) $line);
                }
            }
        }

        return $this;
    }

    public function persist(): self
    {
        $content =
            $this->playfieldWidth . "\r\n" .
            $this->playfieldHeight . "\r\n" .
            $this->backglassWidth . "\r\n" .
            $this->backglassHeight . "\r\n" .
            $this->backglassDisplay . "\r\n" .
            $this->backglassXOffsetRelativeToPlayfieldLeft . "\r\n" .
            $this->backglassYOffsetRelativeToPlayfieldTop . "\r\n" .
            $this->dmdWidth . "\r\n" .
            $this->dmdHeight . "\r\n" .
            $this->dmdXOffsetRelativeToBackglassLeft . "\r\n" .
            $this->dmdYOffsetRelativeToBackglassTop . "\r\n";
        if (!file_put_contents($this->file, $content)) {
            throw new \RuntimeException('Could not write file ' . $this->file);
        }

        return $this;
    }
}
