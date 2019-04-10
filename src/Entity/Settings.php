<?php

namespace App\Entity;

use App\Validator\Exists;
use App\Validator\Writable;
use Symfony\Component\Validator\Constraints as Assert;

class Settings
{
    /**
     * @Assert\NotBlank()
     * @var string
     */
    private $lcpApiKey = '';

    /**
     * @Assert\NotBlank()
     * @Exists()
     * @Writable()
     * @var string
     */
    private $dofPath = '';

    /**
     * @Assert\NotBlank()
     * @Exists()
     * @Writable()
     * @var string
     */
    private $visualPinballPath = '';

    private $ini;

    public function __construct()
    {
        $this->ini = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . '/settings.ini';
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

    public function getDofPath(): ?string
    {
        return $this->dofPath;
    }

    public function getDofConfigPath(): ?string
    {
        return $this->getDofPath() . DIRECTORY_SEPARATOR . 'config';
    }

    public function setDofPath(string $dofPath): self
    {
        $this->dofPath = $dofPath;

        return $this;
    }

    public function getVisualPinballPath(): ?string
    {
        return $this->visualPinballPath;
    }

    public function getVPinMamePath(): ?string
    {
        return $this->getVisualPinballPath() . DIRECTORY_SEPARATOR . 'VPinMAME';
    }

    public function getRomsPath(): ?string
    {
        return $this->getVPinMamePath() . DIRECTORY_SEPARATOR . 'roms';
    }

    public function getAltcolorPath(): ?string
    {
        $altcolor_dir = $this->getVPinMamePath() . DIRECTORY_SEPARATOR . 'altcolor';
        if (!is_dir($altcolor_dir)) {
            mkdir($altcolor_dir);
        }
        return $altcolor_dir;
    }

    public function getAltsoundPath(): ?string
    {
        $altsound_dir = $this->getVPinMamePath() . DIRECTORY_SEPARATOR . 'altsound';
        if (!is_dir($altsound_dir)) {
            mkdir($altsound_dir);
        }
        return $altsound_dir;
    }

    public function getTablesPath(): ?string
    {
        return $this->getVisualPinballPath() . DIRECTORY_SEPARATOR . 'Tables';
    }

    public function setVisualPinballPath(string $visualPinballPath): self
    {
        $this->visualPinballPath = $visualPinballPath;

        return $this;
    }

    public function load() : self
    {
        if (file_exists($this->ini)) {
            $download = parse_ini_file($this->ini, TRUE);
            $this->setLcpApiKey($download['dof']['LCP_APIKEY']);
            $this->setDofPath($download['dof']['path']);
            $this->setVisualPinballPath($download['visualpinball']['path']);
        } else {
            $old = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . '/download.ini';
            if (file_exists($old)) {
                $download = parse_ini_file($old, TRUE);
                $this->setLcpApiKey($download['download']['LCP_APIKEY']);
                $this->setDofPath($download['download']['DOF_CONFIG_PATH']);
            }
        }

        return $this;
    }

    public function persist() : self
    {
        if (!file_put_contents($this->ini,
                "[dof]\r\n" .
                'LCP_APIKEY = ' . $this->getLcpApiKey() . "\r\n" .
                'path = ' . $this->getDofPath() . "\r\n".
                "[visualpinball]\r\n" .
                'path = ' . $this->getVisualPinballPath() . "\r\n"
            )
        ) {
            throw new \RuntimeException('Could not write file ' . $this->ini);
        }

        if (is_writable($this->getDofPath()) && !is_dir($this->getDofConfigPath())) {
            mkdir($this->getDofConfigPath());
        }

        return $this;
    }
}
