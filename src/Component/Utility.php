<?php

namespace App\Component;

use App\Controller\AbstractSettingsController;
use App\Entity\DirectOutputConfig;
use App\Entity\Settings;
use iphis\FineDiff\Diff;
use Symfony\Component\Filesystem\Filesystem;

class Utility
{
    public static function getDiffTables(array $old_files, array $new_files, Settings $settings): array
    {
        $diff = new Diff();
        $diffs = [];
        $portAssignments = $settings->getPortAssignments();

        foreach ($new_files as $file => $content) {
            $directOutputConfig = new DirectOutputConfig($file);
            $directOutputConfig->load();
            $rgb_ports = $directOutputConfig->getRgbPorts();
            $devicdeId = 0;
            if (preg_match('/directoutputconfig(\d+)\.ini$/i', $file, $matches)) {
                $deviceId = $matches[1];
            }
            $old_lines = explode("\r\n", $old_files[$file]);
            $new_lines = explode("\r\n", $content);
            foreach($old_lines as $number => $line) {
                if ($line != ($new_lines[$number] ?? '')) {
                    $old_cells = explode(',', $line);
                    $new_cells = explode(',', $new_lines[$number] ?? '');
                    $num_cells = count($old_cells);
                    $diff_cells = [$old_cells[0]];
                    for ($i = 1; $i < $num_cells; $i++) {
                        $diff_cells[] = $diff->render($old_cells[$i], $new_cells[$i] ?? '');
                    }
                    $header = '';
                    $toy = '';
                    $data = '';

                    $game_name = $diff_cells[0];
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

    public static function getExistingTablesAndBackglassChoices(Settings $settings): array
    {
        $tables = [];
        $backglasses = [];
        $tables_path = $settings->getTablesPath();
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
        return [$tables, $backglasses];
    }

    public static function getExistingPupPacks(Settings $settings, ?array $roms = null): array
    {
        $pupPacks = [];

        $tableMapping = $settings->getTableMapping();
        $path = $settings->getPinUpPacksPath();
        if (file_exists($path) && is_readable($path)) {
            foreach (scandir($path) as $rom) {
                $real_rom = ltrim($rom, '_');
                if ($roms && in_array($real_rom, $roms)) {
                    if (isset($tableMapping[$real_rom]) && is_dir($rom)) {
                        $size = Utility::directorySize($rom);
                        // Require min 2MB to be considered a pack.
                        if ($size > 1) {
                            $pupPacks[$real_rom] = $tableMapping[$rom];
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

        if (!$roms) {
            $candidates = [];
            foreach ($tableMapping as $rom => $table_mapping_name) {
                $distance_table = levenshtein($table_mapping_name, $table);
                $distance_table_without_manufacturer = levenshtein($table_mapping_name, $table_without_manufacturer);
                $distance = $distance_table < $distance_table_without_manufacturer ? $distance_table : $distance_table_without_manufacturer;
                if ($distance < 5) {
                    $candidates[$rom] = $distance;
                }
            }
            asort($candidates, SORT_NUMERIC);
            $roms = array_slice(array_keys($candidates), 0, 5);
        }

        if (!$roms) {
            $candidates = [];
            foreach ($tableMapping as $rom => $table_mapping_name) {
                $distance = levenshtein(substr($table_mapping_name, 0, 6), substr($table, 0, 6));
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
     * @return int directory size in MB
     */
    public static function directorySize(string $dir): int
    {
        $size = 0;
        foreach (glob(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : Utility::directorySize($each);
        }
        return (int) ($size / 1024 / 1024);
    }
}
