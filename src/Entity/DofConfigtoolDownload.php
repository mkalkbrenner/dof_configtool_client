<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class DofConfigtoolDownload
{
    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $lcpApiKey = '';

    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $dofConfigPath = '';

    private $ini;

    public function __construct()
    {
        $this->ini = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . '/download.ini';
    }

    /**
     * @return string
     */
    public function getIni(): string
    {
        return $this->ini;
    }

    public function getLcpApiKey(): ?string
    {
        return $this->lcpApiKey;
    }

    public function setLcpApiKey(string $lcpApiKey): self
    {
        $this->lcpApiKey = $lcpApiKey;

        return $this;
    }

    public function getDofConfigPath(): ?string
    {
        return $this->dofConfigPath;
    }

    public function setDofConfigPath(string $dofConfigPath): self
    {
        $this->dofConfigPath = $dofConfigPath;

        return $this;
    }

    public function load() : self
    {
        if (file_exists($this->ini)) {
            $download = parse_ini_file($this->ini, TRUE);
            $this->setLcpApiKey($download['download']['LCP_APIKEY']);
            $this->setDofConfigPath($download['download']['DOF_CONFIG_PATH']);
        }

        return $this;
    }

    public function persist() : self
    {
        if (!file_put_contents($this->ini,
            "[download]\r\nLCP_APIKEY = " . $this->getLcpApiKey() . "\r\nDOF_CONFIG_PATH = " . $this->getDofConfigPath() . "\r\n")
        ) {
            throw new \RuntimeException('Could not write file ' . $this->ini);
        }

        return $this;
    }
}
