<?php

namespace App\Component;

use App\Entity\DirectOutputConfig;
use App\Entity\Settings;
use iphis\FineDiff\Diff;

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
                        ++$real_port;
                        $colspan = 1;
                        while (in_array($real_port, $rgb_ports[$game_name] ?? [])) {
                            ++$colspan;
                            $header .= '</th><th scope="col" bgcolor="' . (2 == $colspan ? 'green' : 'blue') . '">' .  $real_port++;
                        }
                        $header .= '</th>';

                        if ($port) {
                            $toy .= '<th  scope="col"' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : ''). '>' . ($portAssignments[$deviceId][$real_port] ?? '') . '</th>';
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
}