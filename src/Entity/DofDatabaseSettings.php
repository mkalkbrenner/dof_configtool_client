<?php

namespace App\Entity;

class DofDatabaseSettings extends Settings
{
    public function getLcpApiKey(): ?string
    {
        return hex2bin('7431557773377473443365414e67706c48506763');
    }

    public function getDofConfigPath(): ?string
    {
        $config_path = $this->getDofPath();
        if ($config_path) {
            $config_path .= DIRECTORY_SEPARATOR . 'DofDatabase';
            if (!is_dir($config_path)) {
                mkdir($config_path);
            }
        }
        return $config_path;
    }

    public function persist(): Settings
    {
        return $this;
    }

    public function getPortAssignments() : array
    {
        return [
            30 => [
                1 => 'RGB Undercab Complex MX',
                4 => 'PF Left Flashers MX',
                7 => 'PF Left Effects MX',
                10 => 'PF Back Flashers MX',
                13 => 'PF Back Effects MX',
                16 => 'PF Back Strobe MX',
                19 => 'PF Back Beacon MX',
                22 => 'PF Back PBX MX',
                25 => 'PF Right Flashers MX',
                28 => 'PF Right Effects MX',
                31 => 'Flipper Button MX',
                34 => 'Flipper Button PBX MX',
                37 => 'Magnasave Left MX',
                40 => 'Magnasave Right MX',
            ],
            51 => [
                1 => 'Start Button',
                2 => 'Launch Button',
                3 => 'Authentic Launch Ball',
                4 => 'ZB Launch Ball',
                5 => 'Fire Button',
                6 => 'Extra Ball',
                7 => '10 Bumper Back Left',
                8 => '10 Bumper Back Center',
                9 => '10 Bumper Back Right',
                10 => '10 Bumper Middle Left',
                11 => '10 Bumper Middle Center',
                12 => '10 Bumper Middle Right',
                13 => 'Slingshot Left',
                14 => 'Slingshot Right',
                15 => 'Flipper Left',
                16 => 'Flipper Right',
                17 => '8 Bumper Left',
                18 => '8 Bumper Center',
                19 => '8 Bumper Right',
                20 => '8 Bumper Back',
                21 => 'Knocker',
                22 => 'Shaker',
                23 => 'Gear',
                24 => 'Beacon',
                25 => 'Fan',
                26 => 'Strobe',
                27 => 'Coin',
                28 => 'How to play',
                29 => 'Genre',
                30 => 'Exit',
                31 => 'Bell',
                32 => 'Chime 1',
                33 => 'Chime 2',
                34 => 'Chime 3',
                35 => 'Chime 4',
                36 => 'Chime 5',
                37 => 'Hellball Motor',
                38 => 'Hellball Color',
                41 => '5 Flasher Outside Left',
                44 => '5 Flasher Left',
                47 => '5 Flasher Center',
                50 => '5 Flasher Right',
                53 => '5 Flasher Outside Right',
                56 => '3 Flasher Left',
                59 => '3 Flasher Center',
                62 => '3 Flasher Right',
                65 => 'RGB Flippers',
                68 => 'RGB Left Magnasave',
                71 => 'RGB Right Magnasave',
                74 => 'RGB Undercab Smart',
                77 => 'RGB Undercab Complex',
            ],
        ];
    }
}
