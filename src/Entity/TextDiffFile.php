<?php

namespace App\Entity;

class TextDiffFile extends TextFile
{
    /**
     * @var string
     */
    private $left;

    /**
     * @return string
     */
    public function getLeft(): string
    {
        return $this->left;
    }

    public function setLeft(?string $text): self
    {
        $this->left = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getRight(): string
    {
        return $this->getText();
    }

    public function setRight(?string $text): self
    {
        return $this->setText($text);
    }
}
