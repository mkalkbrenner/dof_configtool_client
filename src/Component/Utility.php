<?php

namespace App\Component;

use App\Entity\DirectOutputConfig;
use App\Entity\Settings;
use iphis\FineDiff\Diff;

class Utility
{
    public static function getDiffTables(array $old_files, array $new_files, Settings $settings, ?array $synonyms = []): array
    {
        $diff = new Diff();
        $diffs = [];
        $portAssignments = $settings->getPortAssignments();

        foreach ($new_files as $file => $content) {
            $directOutputConfig = new DirectOutputConfig($file);
            $directOutputConfig->load()->createSynonymGames($synonyms);
            $rgb_ports = $directOutputConfig->getRgbPorts();
            $devicdeId = 0;
            if (preg_match('/directoutputconfig(\d+)\.ini$/i', $file, $matches)) {
                $deviceId = $matches[1];
            }
            $old_lines = explode("\r\n", $old_files[$file]);
            $new_lines = explode("\r\n", $content);
            foreach($old_lines as $number => $line) {
                $old_cells = explode(',', $line);
                $game_name = $old_cells[0];
                if (array_key_exists($game_name, $synonyms)) {
                    $old_cells = [0 => $game_name] + array_fill(1, count($old_cells) - 1, '0');
                    $line = '';
                }
                if ($line != ($new_lines[$number] ?? '')) {
                    $new_cells = explode(',', $new_lines[$number] ?? '');
                    $num_cells = count($old_cells);
                    $diff_cells = [$old_cells[0]];
                    for ($i = 1; $i < $num_cells; $i++) {
                        $diff_cells[] = $diff->render($old_cells[$i], $new_cells[$i] ?? '');
                    }
                    $header = '';
                    $toy = '';
                    $data = '';

                    $real_port = 0;
                    foreach ($diff_cells as $port => $dof_string) {
                        $dof_string = str_replace(['<ins>', '<del>'], ['<ins class="bg-success">', '<del class="bg-danger">'], $dof_string);

                        $header .= '<th scope="col"' . (in_array($real_port + 1, $rgb_ports[$game_name] ?? []) ? ' bgcolor="red">' : '>') . ($real_port ?: 'ROM \ Port');
                        $device_real_port = $real_port++;
                        $colspan = 1;
                        while (in_array($real_port, $rgb_ports[$game_name] ?? [])) {
                            ++$colspan;
                            $header .= '</th><th scope="col" bgcolor="' . (2 == $colspan ? 'green' : 'blue') . '">' .  $real_port++;
                        }
                        $header .= '</th>';

                        if ($port) {
                            $toy .= '<th  scope="col"' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : ''). '>' . ($portAssignments[$deviceId][$device_real_port] ?? '') . '</th>';
                            $data .= '<td' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : ''). '>' . $dof_string . '</td>';
                        } else {
                            $toy .= '<th scope="row"></th>';
                            $data .= '<th scope="row">' . $dof_string . '</th>';
                        }
                    }
                    $diffs[$directOutputConfig->getDeviceName() . ': ' . basename($file)][] = '<tr>' . $toy . '</tr><tr>' . $header . '</tr><tr>' . $data . '</tr>';
                }
            }
        }
        return $diffs;
    }

    public static function getDofTableRows(string $rom, Settings $settings): array
    {
        $rows = [];
        $files = [];
        if ($dof_config_path = $settings->getDofConfigPath()) {
            foreach (scandir($dof_config_path) as $file) {
                if (preg_match('/^directoutputconfig(\d+)\.ini$/i', $file, $matches)) {
                    $file_path = $settings->getDofConfigPath() . DIRECTORY_SEPARATOR . $matches[0];
                    if (!isset($mods[$file_path])) {
                        $files[$matches[1]] = $file_path;
                    }
                }
            }
        }

        $portAssignments = $settings->getPortAssignments();

        foreach ($files as $deviceId => $file) {
            $directOutputConfig = new DirectOutputConfig($file);
            $directOutputConfig->load();
            $rgb_ports = $directOutputConfig->getRgbPorts();
            $games = $directOutputConfig->getGames();
            while ($rom && !isset($games[$rom])) {
                $rom = substr($rom, 0, -1);
            }

            if ($rom && !empty($games[$rom])) {
                $rows[] = '<tr><th scope="col" colspan="3" bgcolor="#6495ed">' . $directOutputConfig->getDeviceName() . ': ' . basename($file) . '</th>';
                foreach ($games[$rom] as $port => $dof_string) {
                    $row = '<tr>';
                    if (!in_array($port,$rgb_ports[$rom] ?? [])) {
                        $row .= '<th scope="row" bgcolor="' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? 'red' : 'white') . '">' . ($port ?: 'Port') . '</th>';
                        if ($port) {
                            $row .= '<th  scope="row"' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? ' rowspan="3"' : '') . '>' . ($portAssignments[$deviceId][$port] ?? '') . '</th>';
                            $row .= '<td' . (in_array($port + 1, $rgb_ports[$rom] ?? []) ? ' rowspan="3"' : '') . '>' . $dof_string . '</td>';
                        } else {
                            $row .= '<th scope="row">Description</th>';
                            $row .= '<th scope="row">' . $dof_string . '</th>';
                        }
                    } else {
                        $row .= '<th scope="row" bgcolor="' . (in_array($port + 1, $rgb_ports[$rom]) ? 'green' : 'blue') . '">' . $port . '</th>';
                    }

                    $rows[] = $row . '</tr>';
                }
            }
        }
        return $rows;
    }

    public static function getExistingTablesAndBackglassChoices(Settings $settings): array
    {
        $tables = [];
        $backglasses = [];
        if ($tables_path = $settings->getTablesPath()) {
            foreach (scandir($tables_path) as $filename) {
                $basename = preg_replace('/\.vpx/i', '', $filename);
                if ($basename !== $filename) {
                    $tables[md5($filename)] = $basename;
                    continue;
                }
                $basename = preg_replace('/\.directb2s/i', '', $filename);
                if ($basename !== $filename) {
                    // Use $basename . '.directb2s' instead of $filename to ensure lower case file type.
                    $backglasses[$basename] = $basename . '.directb2s';
                }
            }
        }
        return [$tables, $backglasses];
    }

    public static function getExistingPupPacks(Settings $settings, ?array $roms = null): array
    {
        $pupPacks = [];

        $tableMapping = $settings->getTableMapping();
        if ($path = $settings->getPinUpPacksPath()) {
            if (file_exists($path) && is_readable($path)) {
                foreach (scandir($path) as $rom) {
                    if (strpos($rom, '.') !== 0 && stripos($rom, 'PinUpMenu') === false) {
                        $real_rom = ltrim($rom, '_');
                        if (!$roms || in_array($real_rom, $roms)) {
                            $pup_path = $path . DIRECTORY_SEPARATOR . $rom;
                            if (is_dir($pup_path)) {
                                // Require min 2MB to be considered a pack.
                                $size = Utility::directorySize($pup_path, 2);
                                if ($size >= (2 * 1024 * 1024)) {
                                    $pupPacks[$rom] = $tableMapping[$real_rom] ?? $real_rom;
                                }
                            }
                        }
                    }
                }
            }

            array_filter($pupPacks, function ($rom) use ($pupPacks) {
                // Remove inactive packs if a corresponding active pack exists.
                return strpos($rom, '_') !== 0 || !array_key_exists(ltrim($rom, '_'), $pupPacks);
            }, ARRAY_FILTER_USE_KEY);

            asort($pupPacks);
        }

        return $pupPacks;
    }

    public static function getGroupedBackglassChoices(array $choices, string $basename): array
    {
        $group = [
            'Current' => [],
            'Suggested' => [],
            'Enabled' => [],
            'Disabled'=> [],
        ];

        foreach ($choices as $key => $value) {
            if ($key === $basename) {
                $group['Current'][$key] = $value;
            } elseif (strtolower(substr(ltrim($key, '_'), 0, 4)) === strtolower(substr($basename, 0, 4))) {
                $group['Suggested'][$key] = $value;
            } elseif (0 !== strpos($key, '_')) {
                $group['Enabled'][$key] = $value;
            } else {
                $group['Disabled'][$key] = $value;
            }
        }

        return [
                'No Backglass (or PUP Pack used instead)' => '_',
            ] + array_filter($group, 'count');
    }

    public static function getRomsForTable(string $table, Settings $settings): array
    {
        $table = trim($table);
        $table_without_manufacturer = preg_replace('/\s*\([^)]+\)$/', '', $table);

        $roms = [];
        $tableMapping = $settings->getTableMapping();
        foreach ($tableMapping as $rom => $table_mapping_name) {
            if ($table_mapping_name == $table || $table_mapping_name == $table_without_manufacturer) {
                $roms[] = $rom;
            }
        }

        $lower_table = mb_strtolower($table);
        $lower_table_without_manufacturer = mb_strtolower($table_without_manufacturer);

        if (!$roms) {
            $candidates = [];
            foreach ($tableMapping as $rom => $table_mapping_name) {
                $lower_table_mapping_name = mb_strtolower($table_mapping_name);
                $distance_table = levenshtein($lower_table_mapping_name, $lower_table);
                $distance_table_without_manufacturer = levenshtein($lower_table_mapping_name, $lower_table_without_manufacturer);
                $distance = $distance_table < $distance_table_without_manufacturer ? $distance_table : $distance_table_without_manufacturer;
                if ($distance < 5) {
                    $candidates[$rom] = $distance;
                }
            }
            asort($candidates, SORT_NUMERIC);
            $roms = array_slice(array_keys($candidates), 0, 5);
        }

        if (!$roms) {
            $lower_table = substr($lower_table, 0, 6);
            $candidates = [];
            foreach ($tableMapping as $rom => $table_mapping_name) {
                $lower_table_mapping_name = mb_strtolower(substr($table_mapping_name, 0, 6));
                $distance = levenshtein($lower_table_mapping_name, $lower_table);
                if ($distance <= 2) {
                    $candidates[$rom] = $distance;
                }
            }
            asort($candidates, SORT_NUMERIC);
            $roms = array_slice(array_keys($candidates), 0, 5);
        }

        $real_roms = $settings->getRoms();
        return array_values($roms ? array_intersect($roms, $real_roms) : array_intersect(array_keys($tableMapping), $real_roms));
    }

    /**
     * @param string $dir
     * @param int    $min_mb
     *
     * @return int directory size in Byte
     */
    public static function directorySize(string $dir, ?int $min_mb = null): int
    {
        $size = 0;

        foreach (scandir($dir) as $file) {
            if (strpos($file, '.') !== 0) {
                $file_path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($file_path)) {
                    $size += (Utility::directorySize($file_path, $min_mb));
                } elseif (is_file($file_path)) {
                    $size += (filesize($file_path));
                }
                if ($min_mb && $size >= ($min_mb * 1024 * 1024)) {
                    break;
                }
            }
        }

        return $size;
    }

    public static function parseIniString(string $string, ?bool $strip_comments = TRUE): array
    {
        $parsed = parse_ini_string($string, TRUE);
        if ($strip_comments) {
            return array_filter($parsed, static function ($key) {
                $key = trim($key);
                return strpos($key, '#') !== 0;
            }, ARRAY_FILTER_USE_KEY);
        }
        return $parsed;
    }
}
