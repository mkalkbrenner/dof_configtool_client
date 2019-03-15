<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class DofConfigtoolDownload
{
    const DOWNLOAD_INI = __DIR__ . '/../../ini/download.ini';

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
        if (file_exists(self::DOWNLOAD_INI)) {
            $download = parse_ini_file(self::DOWNLOAD_INI, TRUE);
            $this->setLcpApiKey($download['download']['LCP_APIKEY']);
            $this->setDofConfigPath($download['download']['DOF_CONFIG_PATH']);
        }

        return $this;
    }

    public function persist() : self
    {
        if (!file_put_contents(self::DOWNLOAD_INI,
            "[download]\r\nLCP_APIKEY = " . $this->getLcpApiKey() . "\r\nDOF_CONFIG_PATH = " . $this->getDofConfigPath() . "\r\n")
        ) {
            throw new \RuntimeException('Could not write file ' . self::DOWNLOAD_INI);
        }

        return $this;
    }
}
