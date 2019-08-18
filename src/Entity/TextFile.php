<?php

namespace App\Entity;

class TextFile
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $text = '';

    public function __construct(string $directory, string $file)
    {
        if (!is_dir($directory)) {
            mkdir($directory);
        }

        $this->path = $directory . DIRECTORY_SEPARATOR . $file;
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function load(): self
    {
        if (file_exists($this->path)) {
            $this->text = file_get_contents($this->path);
        }

        return $this;
    }

    public function persist(): self
    {
        if (!file_put_contents($this->path, $this->text)) {
            throw new \RuntimeException('Could not write file ' . $this->path);
        }

        return $this;
    }
}
