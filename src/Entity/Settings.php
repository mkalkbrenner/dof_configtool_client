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

    /**
     * @var bool
     */
    private $versionControl = false;

    /**
     * @var string
     */
    private $gitBinary;

    /**
     * @var string
     */
    private $bsPatchBinary;

    /**
     * @var string
     */
    private $gitUser = 'DOF Configtool Client';

    /**
     * @var string
     */
    private $gitEmail = 'mk47@localhost';

    private $ini;

    public function __construct()
    {
        $this->ini = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . '/settings.ini';

        // Default for Unix and Windows custom installs, where the binaries should be in PATH.
        $this->gitBinary = 'git';
        $this->bsPatchBinary = 'bspatch';

        if (extension_loaded('com_dotnet')) {
            $this->gitBinary .= '.exe';
            $this->bsPatchBinary .= '.exe';

            $gitBinary = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'PortableGit' . DIRECTORY_SEPARATOR. 'bin' . DIRECTORY_SEPARATOR . 'git.exe';
            if (file_exists($gitBinary)) {
                $this->gitBinary = $gitBinary;
            }

            $bsPatchBinary = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'bsdiff_win_exe' . DIRECTORY_SEPARATOR. 'bspatch.exe';
            if (file_exists($bsPatchBinary)) {
                $this->bsPatchBinary = $bsPatchBinary;
            }
        }
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
        $config_path = $this->getDofPath() . DIRECTORY_SEPARATOR . 'Config';
        if (!is_dir($config_path)) {
            mkdir($config_path);
        }
        return $config_path;
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

    public function isVersionControl(): ?bool
    {
        return $this->versionControl;
    }

    public function setVersionControl(bool $versionControl): self
    {
        $this->versionControl = $versionControl;

        return $this;
    }

    /**
     * @return string
     */
    public function getGitBinary(): string
    {
        return $this->gitBinary;
    }

    /**
     * @param string $gitBinary
     * @return self
     */
    public function setGitBinary(string $gitBinary): self
    {
        $this->gitBinary = $gitBinary;
        return $this;
    }

    /**
     * @return string
     */
    public function getGitUser(): string
    {
        return $this->gitUser;
    }

    /**
     * @param string $gitUser
     * @return self
     */
    public function setGitUser(string $gitUser): self
    {
        $this->gitUser = $gitUser;
        return $this;
    }

    /**
     * @return string
     */
    public function getGitEmail(): string
    {
        return $this->gitEmail;
    }

    /**
     * @param string $gitEmail
     * @return self
     */
    public function setGitEmail(string $gitEmail): self
    {
        $this->gitEmail = $gitEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getBsPatchBinary(): string
    {
        return $this->bsPatchBinary;
    }

    /**
     * @param string $bsPatchBinary
     * @return self
     */
    public function setBsPatchBinary(string $bsPatchBinary): self
    {
        $this->bsPatchBinary = $bsPatchBinary;
        return $this;
    }

    public function load(): self
    {
        if (file_exists($this->ini)) {
            $download = parse_ini_file($this->ini, TRUE);
            $this->setLcpApiKey($download['dof']['LCP_APIKEY']);
            $this->setDofPath($download['dof']['path']);
            $this->setVisualPinballPath($download['visualpinball']['path']);
            $this->setVersionControl((bool) ($download['git']['enabled'] ?? false));
            $this->setGitBinary($download['git']['binary'] ?? $this->getGitBinary());
            $this->setGitUser($download['git']['user'] ?? $this->getGitUser());
            $this->setGitEmail($download['git']['email'] ?? $this->getGitEmail());
            $this->setBsPatchBinary($download['bsdiff']['bspatch_binary'] ?? $this->getBsPatchBinary());
        } else {
            // 0.1.x backward compatibility
            $old = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . '/download.ini';
            if (file_exists($old)) {
                $download = parse_ini_file($old, TRUE);
                $this->setLcpApiKey($download['download']['LCP_APIKEY']);
                $this->setDofPath($download['download']['DOF_CONFIG_PATH']);
            }
        }

        return $this;
    }

    public function persist(): self
    {
        if (!file_put_contents($this->ini,
                "[dof]\r\n" .
                'LCP_APIKEY = "' . trim($this->getLcpApiKey(), '" ') . '"' . "\r\n" .
                'path = "' . trim($this->getDofPath(), '" ') . '"' . "\r\n".
                "[visualpinball]\r\n" .
                'path = "' . trim($this->getVisualPinballPath(), '" ') . '"' . "\r\n" .
                "[git]\r\n" .
                'enabled = ' . (int) $this->isVersionControl()  . "\r\n" .
                'binary = "' . trim($this->getGitBinary(), '" ') . '"' . "\r\n" .
                'user = "' . trim($this->getGitUser(), '" ') . '"' . "\r\n" .
                'email = "' . trim($this->getGitEmail(), '" ') . '"' . "\r\n" .
                "[bsdiff]\r\n" .
                'bspatch_binary = "' . trim($this->getBsPatchBinary(), '" ') . '"' . "\r\n"
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
