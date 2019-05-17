<?php

namespace App\Entity;

class DirectOutputConfig
{
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

    public function __construct(string $file)
    {
        $this->file = $file;
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

    public function load(): self
    {
        if ($contents = file_get_contents($this->file)) {
            // Normalize line endings.
            $this->content = preg_replace('/\R/', "\r\n", $contents);
            list($this->head, $this->config) = explode('[Config DOF]', $this->content);
            $color_section = FALSE;
            $variable_section = FALSE;
            foreach (explode("\r\n", $this->head) as $line) {
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
