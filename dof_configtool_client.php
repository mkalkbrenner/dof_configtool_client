<?php

$additional_durations = [
	#port => duration
	23 => 100,
	26 => 100,
];

$file = 'directoutputconfig/directoutputconfig51.ini';
	
list($head, $config) = explode('[Config DOF]', file_get_contents($file));
$games = [];
foreach (explode("\n", $config) as $game_row) {
	$game = str_getcsv($game_row);	
	foreach ($additional_durations as $port => $duration) {
		if (isset($game[$port])) {
			$triggers = explode('/', $game[$port]);
			foreach ($triggers as &$trigger) {
				$trigger = preg_replace('/([SWE]\d+$)/', '$1 ' . $duration, $trigger);
			}
			$game[$port] = implode('/', $triggers);
		}
	}
	$games[] = implode(',', $game);
}

file_put_contents($file, $head . '[Config DOF]' . implode("\n", $games));
