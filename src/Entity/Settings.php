<?php

namespace App\Entity;

use App\Validator\Exists;
use App\Validator\Writable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;

class Settings
{
    const DEV_TXT = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' .  DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'dev.txt';

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
     * @var string
     */
    private $tablesPath = '';

    /**
     * @var string
     */
    private $pinballYPath = '';

    /**
     * @var string
     */
    private $pinUpSystemPath = '';

    /**
     * @var array
     */
    private $portAssignments = [];

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

    private $debug = false;

    private $ini;

    public function __construct()
    {
        $this->ini = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . '/settings.ini';

        if (extension_loaded('com_dotnet')) {
            $gitBinary = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'PortableGit' . DIRECTORY_SEPARATOR. 'bin' . DIRECTORY_SEPARATOR . 'git.exe';
            if (file_exists($gitBinary)) {
                $this->gitBinary = $gitBinary;
            }

            $bsPatchBinary = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'bsdiff_win_exe' . DIRECTORY_SEPARATOR. 'bspatch.exe';
            if (file_exists($bsPatchBinary)) {
                $this->bsPatchBinary = $bsPatchBinary;
            }
        } else {
            // Default for Unix where the binaries should be in PATH.
            $this->gitBinary = 'git';
            $this->bsPatchBinary = 'bspatch';
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
        $config_path = null;
        $path = $this->getDofPath();
        if ($path && is_dir($path)) {
            $config_path = $path . DIRECTORY_SEPARATOR . 'Config';
            if (!is_dir($config_path)) {
                mkdir($config_path);
            }
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

    public function getPinballYPath(): ?string
    {
        return $this->pinballYPath;
    }

    public function getPinballYDatabasePath(): ?string
    {
        return $this->pinballYPath ? $this->pinballYPath . DIRECTORY_SEPARATOR . 'Databases' : null;
    }

    public function getPinballYVPXDatabaseFile(): ?string
    {
        $path = $this->getPinballYDatabasePath();
        $path = $path ? $path . DIRECTORY_SEPARATOR . 'Visual Pinball X' . DIRECTORY_SEPARATOR . 'Visual Pinball X.xml' : null;
        return ($path && file_exists($path)) ? $path : null;
    }

    public function getPinUpSystemPath(): ?string
    {
        return $this->pinUpSystemPath;
    }

    public function getPinUpPacksPath(): ?string
    {
        return $this->pinUpSystemPath ? $this->pinUpSystemPath . DIRECTORY_SEPARATOR . 'PUPVideos' : null;
    }

    public function getVPinMamePath(): ?string
    {
        $path = $this->getVisualPinballPath();
        return $path ? $path . DIRECTORY_SEPARATOR . 'VPinMAME': null;
    }

    public function getRomsPath(): ?string
    {
        $path = $this->getVPinMamePath();
        return $path ? $path . DIRECTORY_SEPARATOR . 'roms' : null;
    }

    public function getRoms(): array
    {
        $roms = [];
        if ($path = $this->getRomsPath()) {
            foreach (scandir($path) as $filename) {
                if (preg_match('/(.+)\.zip$/i', $filename, $matches)) {
                    $roms[] = strtolower($matches[1]);
                }
            }
        }
        return $roms;
    }

    public function getAliasRoms(): array
    {
        $roms = [];
        if ($path = $this->getVPinMamePath()) {
            if (($handle = fopen($path . DIRECTORY_SEPARATOR . 'VPMAlias.txt', 'r')) !== false) {
                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    $rom = array_pop($data);
                    foreach ($data as $alias) {
                        $roms[$alias] = $rom;
                    }
                }
                fclose($handle);
            }
        }
        return $roms;
    }

    public function getAltcolorPath(): ?string
    {
        $altcolor_dir = null;
        $path = $this->getVPinMamePath();
        if ($path && is_dir($path)) {
            $altcolor_dir = $path . DIRECTORY_SEPARATOR . 'altcolor';
            if (!is_dir($altcolor_dir)) {
                mkdir($altcolor_dir);
            }
        }
        return $altcolor_dir;
    }


    public function getAltcolorRoms(): array
    {
        $roms = [];
        if ($path = $this->getAltcolorPath()) {
            foreach (scandir($path) as $filename) {
                if (is_dir($this->getAltcolorPath() . DIRECTORY_SEPARATOR . $filename)) {
                    $roms[] = strtolower($filename);
                }
            }
        }
        return $roms;
    }

    public function getAltsoundPath(): ?string
    {
        $altsound_dir = null;
        $path = $this->getVPinMamePath();
        if ($path && is_dir($path)) {
            $altsound_dir = $path . DIRECTORY_SEPARATOR . 'altsound';
            if (!is_dir($altsound_dir)) {
                mkdir($altsound_dir);
            }
        }
        return $altsound_dir;
    }


    public function getAltsoundRoms(): array
    {
        $roms = [];
        if ($path = $this->getAltsoundPath()) {
            foreach (scandir($path) as $filename) {
                if (is_dir($this->getAltsoundPath() . DIRECTORY_SEPARATOR . $filename)) {
                    $roms[] = strtolower($filename);
                }
            }
        }
        return $roms;
    }

    public function getTablesPath(): ?string
    {
        if (!empty($this->tablesPath)) {
            return $this->tablesPath;
        }
        elseif ($visual_pinball_path = $this->getVisualPinballPath()) {
            return $visual_pinball_path . DIRECTORY_SEPARATOR . 'Tables';
        }
        return null;
    }

    /**
     * @return array [rom => Table]
     */
    public function getTableMapping(): array
    {
        static $tableMapping = [];
        if (!$tableMapping) {
            $mappingFile = $this->getDofConfigPath() . DIRECTORY_SEPARATOR . 'tablemappings.xml';
            if (file_exists($mappingFile)) {
                // Normalize line endings.
                $mapping = preg_replace('/\R/', "\r\n", file_get_contents($mappingFile));
                $table = '';
                foreach (explode("\r\n", $mapping) as $line) {
                    if (preg_match('@<TableName>(.*)</TableName>@', $line, $matches)) {
                        $table = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5);
                    } elseif (preg_match('@<RomName>(.*)</RomName>@', $line, $matches)) {
                        $tableMapping[trim($matches[1])] = $table;
                    }
                }
            }

            foreach ($this->getRoms() as $real_rom) {
                if (!isset($tableMapping[$real_rom])) {
                    foreach ($tableMapping as $rom => $table) {
                        $real_rom_prefix = preg_replace('/_.*/', '', $real_rom);
                        if ($real_rom_prefix === $rom) {
                            $tableMapping[$real_rom] = $table;
                            break;
                        }
                        if (strpos($real_rom, $rom) === 0) {
                            $tableMapping[$real_rom] = $table;
                            // No break. A later prefix match should be able to overwrite this hit.
                        }
                    }
                }
            }
        }
        return $tableMapping;
    }

    public function setVisualPinballPath(string $visualPinballPath): self
    {
        $this->visualPinballPath = $visualPinballPath;

        return $this;
    }

    public function setTablesPath(?string $tables): self
    {
        $this->tablesPath = $tables;
        return $this;
    }

    public function setPinballYPath(?string $pinballYPath): self
    {
        $this->pinballYPath = $pinballYPath;

        return $this;
    }

    public function setPinUpSystemPath(?string $pinUpSystemPath): self
    {
        $this->pinUpSystemPath = $pinUpSystemPath;

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
    public function getGitBinary(): ?string
    {
        return $this->gitBinary;
    }

    /**
     * @param string $gitBinary
     * @return self
     */
    public function setGitBinary(?string $gitBinary): self
    {
        $this->gitBinary = $gitBinary;
        return $this;
    }

    /**
     * @return string
     */
    public function getGitUser(): ?string
    {
        return $this->gitUser;
    }

    /**
     * @param string $gitUser
     * @return self
     */
    public function setGitUser(?string $gitUser): self
    {
        $this->gitUser = $gitUser;
        return $this;
    }

    /**
     * @return string
     */
    public function getGitEmail(): ?string
    {
        return $this->gitEmail;
    }

    /**
     * @param string $gitEmail
     * @return self
     */
    public function setGitEmail(?string $gitEmail): self
    {
        $this->gitEmail = $gitEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getBsPatchBinary(): ?string
    {
        return $this->bsPatchBinary;
    }

    /**
     * @param string $bsPatchBinary
     * @return self
     */
    public function setBsPatchBinary(?string $bsPatchBinary): self
    {
        $this->bsPatchBinary = $bsPatchBinary;
        return $this;
    }

    public function getRgbToys() : ?array
    {
        return [
            'RGB Undercab Complex MX',
            'PF Left Flashers MX',
            'PF Left Effects MX',
            'PF Back Flashers MX',
            'PF Back Effects MX',
            'PF Back Strobe MX',
            'PF Back Beacon MX',
            'PF Back PBX MX',
            'PF Right Flashers MX',
            'PF Right Effects MX',
            'Flipper Button MX',
            'Flipper Button PBX MX',
            'Magnasave Left MX',
            'Magnasave Right MX',
            'Hellball Color',
            '5 Flasher Outside Left',
            '5 Flasher Left',
            '5 Flasher Center',
            '5 Flasher Right',
            '5 Flasher Outside Right',
            '3 Flasher Left',
            '3 Flasher Center',
            '3 Flasher Right',
            'RGB Flippers',
            'RGB Left Magnasave',
            'RGB Right Magnasave',
            'RGB Undercab Smart',
            'RGB Undercab Complex',
        ];
    }

    public function getPortAssignments(): array
    {
        return $this->portAssignments;
    }

    public function setPortAssignments(array $portAssignments): self
    {
        $this->portAssignments = $portAssignments;

        return $this;
    }

    public function getPortsByToy(string $name): array
    {
        $found = [];
        foreach ($this->portAssignments as $device => $ports) {
            foreach ($ports as $port => $toy) {
                if ($name === $toy) {
                    $found[$device][] = $port;
                }
            }
        }
        return $found;
    }

    public function __get(string $name): array
    {
        if (preg_match('/^(\d+)_(\d+)$/', $name, $matches)) {
            return explode('|', $this->portAssignments[$matches[1]][$matches[2]] ?? '');
        }
    }

    public function __set(string $name, $values): self
    {
        if (preg_match('/^(\d+)_(\d+)$/', $name, $matches)) {
            $this->portAssignments[$matches[1]][$matches[2]] = implode('|', $values);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return self
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function load(): self
    {
        if (file_exists($this->ini)) {
            $settings = parse_ini_file($this->ini, TRUE);
            $this->setLcpApiKey($settings['dof']['LCP_APIKEY']);
            $this->setDofPath($settings['dof']['path']);
            $this->setVisualPinballPath($settings['visualpinball']['path']);
            $this->setTablesPath($settings['visualpinball']['tables_path'] ?? '');
            $this->setPinballYPath($settings['pinbally']['path'] ?? '');
            $this->setPinUpSystemPath($settings['pinupsystem']['path'] ?? '');
            $this->setVersionControl((bool) ($settings['git']['enabled'] ?? false));
            $this->setGitBinary(!empty($settings['git']['binary']) ? $settings['git']['binary'] : $this->getGitBinary());
            $this->setGitUser($settings['git']['user'] ?? $this->getGitUser());
            $this->setGitEmail($settings['git']['email'] ?? $this->getGitEmail());
            $this->setBsPatchBinary(!empty($settings['bsdiff']['bspatch_binary']) ? $settings['bsdiff']['bspatch_binary'] : $this->getBsPatchBinary());
            $this->setPortAssignments($settings['portassignments'] ?? []);
        } else {
            // 0.1.x backward compatibility
            $old = ($_SERVER['PROGRAM_DATA'] ?? (__DIR__ . '/../../ini')) . DIRECTORY_SEPARATOR . 'download.ini';
            if (file_exists($old)) {
                $settings = parse_ini_file($old, TRUE);
                $this->setLcpApiKey($settings['download']['LCP_APIKEY']);
                $this->setDofPath($settings['download']['DOF_CONFIG_PATH']);
            }
        }

        $this->setDebug(file_exists(self::DEV_TXT));

        return $this;
    }

    public function persist(): self
    {
        $content =
            "[dof]\r\n" .
            'LCP_APIKEY = "' . addslashes(trim($this->getLcpApiKey(), '" ')) . '"' . "\r\n" .
            'path = "' . addslashes(trim($this->getDofPath(), '" ')) . '"' . "\r\n".
            "[visualpinball]\r\n" .
            'path = "' . addslashes(trim($this->getVisualPinballPath(), '" ')) . '"' . "\r\n" .
            'tables_path = "' . addslashes(trim($this->getTablesPath(), '" ')) . '"' . "\r\n" .
            "[pinbally]\r\n" .
            'path = "' . addslashes(trim($this->getPinballYPath(), '" ')) . '"' . "\r\n" .
            "[pinupsystem]\r\n" .
            'path = "' . addslashes(trim($this->getPinUpSystemPath(), '" ')) . '"' . "\r\n" .
            "[git]\r\n" .
            'enabled = ' . (int) $this->isVersionControl()  . "\r\n" .
            'binary = "' . addslashes(trim($this->getGitBinary(), '" ')) . '"' . "\r\n" .
            'user = "' . addslashes(trim($this->getGitUser(), '" ')) . '"' . "\r\n" .
            'email = "' . addslashes(trim($this->getGitEmail(), '" ')) . '"' . "\r\n" .
            "[bsdiff]\r\n" .
            'bspatch_binary = "' . addslashes(trim($this->getBsPatchBinary(), '" ')) . '"' . "\r\n";

        if ($portAssignments = $this->getPortAssignments()) {
            $content .= "[portassignments]\r\n";
            foreach ($portAssignments as $deviceId => $ports) {
                foreach ($ports as $port => $toy) {
                    $content .= $deviceId . '[' . $port . ']' . ' = "' . $toy . '"' . "\r\n";
                }
            }
        }

        if (!file_put_contents($this->ini, $content)) {
            throw new \RuntimeException('Could not write file ' . $this->ini);
        }

        if (is_writable($this->getDofPath()) && !is_dir($this->getDofConfigPath())) {
            mkdir($this->getDofConfigPath());
        }

        if ($this->isDebug()) {
            file_put_contents(self::DEV_TXT, DOFCTC_VERSION);
        } else {
            @unlink(self::DEV_TXT);
        }

        return $this;
    }
}
