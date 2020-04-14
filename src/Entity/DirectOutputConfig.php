<?php

namespace App\Entity;

class DirectOutputConfig
{
    const FILE_PATERN = '/directoutputconfig(\d*)\.ini$/i';

    /**
     * @var int
     */
    private $version = 0;

    /**
     * @var string
     */
    private $head = '';

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var array
     */
    private $colors = [];

    /**
     * @var array
     */
    private $variables = [];

    /**
     * @var string
     */
    private $config = '';

    /**
     * @var array
     */
    private $rgbPorts = [];

    /**
     * @var array
     */
    private $games = [];

    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $deviceId;

    public function __construct(string $file)
    {
        $this->file = str_replace('directoutputconfig0.', 'directoutputconfig.', $file);
        preg_match(self::FILE_PATERN, $file, $matches);
        $this->deviceId = (int) $matches[1];
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getHead(): string
    {
        return $this->head;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return self
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): self
    {
        $this->colors = $colors;
        return $this;
    }

    public function getColorNamesAndVariables(): array
    {
        return array_merge(
            array_keys($this->getColors()),
            array_keys(array_filter($this->getVariables(), static function ($value) {
                return strpos($value, 'SHP') !== false;
            }))
        );
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfig(): string
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getGames(): array
    {
        return $this->games;
    }

    /**
     * @return array
     */
    public function getRgbPorts(): array
    {
        return $this->rgbPorts;
    }

    public function getDevices(): array
    {
        return [
            0 => 'Ledwiz 1',
            2 => 'Ledwiz 2',
            3 => 'Ledwiz 3',
            4 => 'Ledwiz 4',
            5 => 'Ledwiz 5',
            6 => 'Ledwiz 6',
            8 => 'FRDM-KL25Z 1',
            19 => 'PacDrive 1',
            20 => 'PacLed 1',
            21 => 'PacLed 2',
            22 => 'PacLed 3',
            23 => 'PacLed 4',
            27 => 'Ultimate/IO 1',
            28 => 'Ultimate/IO 2',
            30 => 'WS2811 1',
            31 => 'WS2811 2',
            32 => 'WS2811 3',
            33 => 'WS2811 4',
            40 => 'SainSmart 1',
            41 => 'SainSmart 2',
            42 => 'SainSmart 3',
            43 => 'SainSmart 4',
            44 => 'SainSmart 5',
            45 => 'SainSmart 6',
            46 => 'SainSmart 7',
            47 => 'SainSmart 8',
            48 => 'SainSmart 9',
            49 => 'SainSmart 10',
            51 => 'Pinscape 1',
            52 => 'Pinscape 2',
            53 => 'Pinscape 3',
            54 => 'Pinscape 4',
            55 => 'Pinscape 5',
            56 => 'Pinscape 6',
            57 => 'Pinscape 7',
            58 => 'Pinscape 8',
            59 => 'Pinscape 9',
            60 => 'Pinscape 10',
            61 => 'Pinscape 11',
            62 => 'Pinscape 12',
            63 => 'Pinscape 13',
            64 => 'Pinscape 14',
            65 => 'Pinscape 15',
            70 => 'Philips_Hue 1',
            71 => 'Philips_Hue 2',
            80 => 'Pincontrol1 1',
            85 => 'Pincontrol2 1',
            100 => 'Artnet 1',
            101 => 'Artnet 2',
            102 => 'Artnet 3',
            103 => 'Artnet 4',
            104 => 'Artnet 5',
            105 => 'Artnet 6',
            106 => 'Artnet 7',
            107 => 'Artnet 8',
            108 => 'Artnet 9',
            109 => 'Artnet 10',
            110 => 'Artnet 11',
            111 => 'Artnet 12',
            112 => 'Artnet 13',
            113 => 'Artnet 14',
            114 => 'Artnet 15',
            115 => 'Artnet 16',
        ];
    }

    public function getDeviceName(): string
    {
        return $this->getDevices()[$this->getDeviceId()];
    }

    public function getDeviceId(): int
    {
        return $this->deviceId;
    }

    public function createSynonymGames(array $synonyms): self
    {
        foreach ($synonyms as $synonym => $original) {
            $s = trim($synonym);
            $o = trim($original);
            if (isset($this->games[$o]) && !isset($this->games[$s])) {
                $values = array_reverse($this->games);
                $empty_lines = [];
                foreach ($values as $key => $value) {
                    if (!$value) {
                        $empty_lines[] = $value;
                        array_pop($this->games);
                    } else {
                        break;
                    }
                }
                $this->games[$s] = $this->games[$o];
                // The ROM name is stored in index 0.
                $this->games[$s][0] = $s;
                array_push($this->games, ...$empty_lines);
                $this->rgbPorts[$s] = $this->rgbPorts[$o];

                if (preg_match("/\r\n" . preg_quote($o, '/') . "(.*?)\r\n/", $this->config, $matches)) {
                    $this->config = trim($this->config) . "\r\n" . $s .$matches[1] . "\r\n";
                    $this->content = trim($this->content) . "\r\n" . $s .$matches[1] . "\r\n";
                }
            }
        }
        return $this;
    }

    public function load(?string $contents = null): self
    {
        if ($contents || ($contents = file_get_contents($this->file))) {
            // Normalize line endings.
            $this->content = preg_replace('/\R/', "\r\n", $contents);
            list($this->head, $this->config) = explode('[Config DOF]', $this->content);
            $color_section = FALSE;
            $variable_section = FALSE;
            foreach (explode("\r\n", $this->head) as $line) {
                if (strpos($line, 'version=') === 0 && preg_match('/\d+/', $line, $matches)) {
                    $this->version = (int) $matches[0];
                    continue;
                }
                if (strpos($line, '[Colors DOF]') === 0) {
                    $color_section = TRUE;
                    $variable_section = FALSE;
                    continue;
                }
                if (strpos($line, '[Variables DOF]') === 0) {
                    $color_section = FALSE;
                    $variable_section = TRUE;
                    continue;
                }
                if (strpos($line, '[') === 0) {
                    $color_section = FALSE;
                    $variable_section = FALSE;
                    continue;
                }

                if ($color_section) {
                    if (strpos($line, '=#')) {
                        list($color_name, $color_value) = explode('=', $line);
                        $this->colors[$color_name] = substr($color_value, 0, 7);
                    }
                }
                if ($variable_section) {
                    if (strpos($line, '=')) {
                        list($variable_name, $variable_value) = explode(' = ', $line);
                        $this->variables[$variable_name] = trim($variable_value);
                    }
                }
            }

            $colorIndicators = $this->getColorNamesAndVariables();

            foreach (explode("\r\n", $this->config) as $game_row) {
                if (preg_match('/^Pinball[XY]/', $game_row, $matches)) {
                    // Don't modify PinballX and PinballY settings. They use a different scheme and would require
                    // too many exceptions for now.
                    $this->games[$matches[0]] = $game_row;
                    continue;
                }

                if ($game_row = trim($game_row)) {
                    $game_row_elements = str_getcsv($game_row);
                    $game_name = $game_row_elements[0];
                    $this->games[$game_name] = [];
                    $this->rgbPorts[$game_name] = [];
                    $real_port = 0;
                    foreach ($game_row_elements as $port => $game_row_element) {
                        $this->games[$game_name][$real_port] = trim($game_row_element);
                        if ($real_port) { // Skip Rom name on port 0.
                            foreach ($colorIndicators as $color) {
                                if (stripos($game_row_element, $color) !== FALSE || strpos($game_row_element, '#') !== FALSE) {
                                    $this->games[$game_name][++$real_port] = 0;
                                    $this->rgbPorts[$game_name][] = $real_port;
                                    $this->games[$game_name][++$real_port] = 0;
                                    $this->rgbPorts[$game_name][] = $real_port;
                                    break;
                                }
                            }
                            if ('0' === $this->games[$game_name][$real_port]) {
                                $this->games[$game_name][$real_port] = 0;
                            }
                        }
                        ++$real_port;
                    }
                } else {
                    // Add an "empty line" to ease the diff on modifications.
                    $this->games[] = '';
                }
            }
        }

        return $this;
    }

    public function persist(): self
    {
        if (!file_put_contents($this->file, $this->getContent())) {
            throw new \RuntimeException('Could not write file ' . $this->file);
        }

        return $this;
    }
}
