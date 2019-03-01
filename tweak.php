<?php

$download = parse_ini_file(__DIR__ . '/download.ini', TRUE);
$config_path = trim($download['general']['DOF_CONFIG_PATH'], DIRECTORY_SEPARATOR);

$tweaks = parse_ini_file(__DIR__ . '/tweaks.ini', TRUE);

foreach ($tweaks as $config_file => $adjustments) {
  $file = $config_path . DIRECTORY_SEPARATOR . $config_file;
  if ($contents = file_get_contents($file)) {
    list($head, $config) = explode('[Config DOF]', $contents);
    $games = [];
    foreach (explode("\n", $config) as $game_row) {
      $game = str_getcsv($game_row);
      foreach ($adjustments as $name => $settings) {
        switch ($name) {
          case 'effect_duration':
            foreach ($settings as $port => $duration) {
              if (isset($game[$port])) {
                $triggers = explode('/', $game[$port]);
                foreach ($triggers as &$trigger) {
                  $trigger = preg_replace('/([SWE]\d+$)/', '$1 ' . $duration, $trigger);
                }
                $game[$port] = implode('/', $triggers);
              }
            }
            break;
        }
      }
      $games[] = implode(',', $game);
    }
    file_put_contents($file, $head . '[Config DOF]' . implode("\n", $games));
  }
}
