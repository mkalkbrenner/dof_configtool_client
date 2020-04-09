<?php

namespace App\Entity;

class DmdDevice
{
    use TrackChangesTrait;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $settings = '';

    /**
     * @var string
     */
    private $settingsParsed = [];

    /**
     * @param string $file
     * @return ScreenRes
     */
    public function setPath(string $path): self
    {
        $this->file = $path . DIRECTORY_SEPARATOR . 'DmdDevice.ini';
        return $this;
    }

    public function getSettings(): ?string
    {
        return $this->settings;
    }

    public function getSettingsParsed(): ?array
    {
        if ($this->settings) {
            $this->settingsParsed = parse_ini_string($this->settings, true, INI_SCANNER_RAW);
            return $this->settingsParsed;
        }
        return null;
    }

    public function setSettings(?string $settings): self
    {
        $settings = $settings ?? "\r\n";
        if ($this->trackChanges && trim($this->settings, "\r\n") !== trim($settings, "\r\n")) {
            $this->hasChanges = true;
        }
        $this->settings = $settings;

        return $this;
    }

    public function setSettingsParsed(array $settings): self
    {
        $this->settingsParsed = $settings;
        return $this->setSettings($this->serializeSettingsParsed($settings));
    }

    private function serializeSettingsParsed(array $settings): string
    {
        $string = '';
        foreach ($settings as $section => $configs) {
            $string .= '[' . $section . "]\r\n";
            foreach ($configs as $key => $value) {
               $string .= $key . ' = ' . $value . "\r\n";
            }
        }
        return  $string . "\r\n";
    }

    public function load(): self
    {
        if (file_exists($this->file)) {
            if ($contents = file_get_contents($this->file)) {
                // Normalize line endings.
                $this->settings = preg_replace('/\R/', "\r\n", $contents);
                unset($this->hasChanges);
            }
        }
        return $this;
    }

    public function persist(): self
    {
        if (!$this->trackChanges || $this->hasChanges()) {
            if (!file_put_contents($this->file, $this->settings)) {
                throw new \RuntimeException('Could not write file ' . $this->file);
            }
        }

        return $this;
    }

    public function isEnabled(string $rom, string $device): bool
    {
        if (isset($this->settingsParsed[$rom][$device .' enabled'])) {
            return $this->settingsParsed[$rom][$device .' enabled'] === 'true';
        } elseif(isset($this->settingsParsed[$device]['enabled'])) {
            return $this->settingsParsed[$device]['enabled'] === 'true';
        }
        return false;
    }
}
