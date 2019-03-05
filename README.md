# DOF Configtool Client

This is a client for downloading your config files from http://configtool.vpuniverse.com and for applying additional tweaks to them.

## Motivation

The DOF Configtool is a great tool that covers most of the use-cases for the majority of it's users.
But DOF itself is more powerful. If you want to achieve special things, you can to that using the DOF Configtool by adjusting the indiviual table settings.
But doing so has to downsides:
* you might have to repeat such an adjustment for a lot or all games
* as soon as you have done that, you're decoupled from the upstream (the centralized table database) and you need to track changes manually

Both downsides could be avoided if we add more layers of configuration to the DOF Configtool, for example per port or per toy.  
Since it's currently not possible to contribute to the DOF Configtool directly this client aims to add this missing layers by introducing a new `tweaks.ini`
config file and a client that downloads your pre-configured configuration files from the DOF Configtool and tweaks them accordingly.

### Notes

This client will never replace the awesome DOF Configtool!
But whenever you need to make an individual adjustment for _all_ tables for a specific port of an output controller, this tool might become useful﻿ 😉

In addition to the available tweaks described below you can think of various other tweaks,
for example inverting an effect or anything else described at http://directoutput.github.io/DirectOutput/inifiles.html
Just open an issue if you have an idea or require something special.

The client is written in PHP because of the fact that the configtool itself is written in PHP.
That will hopefully ease an adoption of (some) features by the DOF configtool itself in the future.
Due to the fact that PHP is uncommon for most Windows users, the client doesn't use any PHP frameworks or libraries but plain PHP to keep the setup as simple as possible. 

## Download your configs

### Setup

You need to install PHP on your system. For Windows have a look at https://windows.php.net/download/ or http://php.net/manual/en/install.windows.php

Download the DOF Configtool Client from https://github.com/mkalkbrenner/dof_configtool_client

Rename or copy `download.ini.example` to `download.ini` and adjust the file to your needs:
 * Add your API Key which you obtain from the DOF Configtool Account Settings as `LCP_APIKEY`
 * Adjust `DOF_CONFIG_PATH` to your needs. The downloaded config files will be extracted into this directory.
 
### Usage

Just execute the DOF Configtool Client `download.php` script. It will download your individual config files and extract them to the directory as configured in `download.ini`.

Linux / macOS:
```
php download.php
```

Windows
```
php.exe download.php
```

**_Warning:_** Existing files will be overwritten.
 
## Apply tweaks

### Setup

Rename or copy `tweaks.ini.example` to `tweaks.ini` and adjust the file to your needs. See the next section for the available tweaks.

### Available tweaks

#### default_effect_duration

The standard DOF configs define global and individual effect durations. Individual durations need to be set per game per output controller port per trigger.

Using the DOF configtool it's impossible to set a different default duration then the global one on a specific port. You would have to do that for all tables.

Using this option you can set such an per port default duration for all games at once. But this will only happen if there's not yet set an individual duration.
So `effect_duration[23] = 100` sets the duration to _100ms_ for all triggers on output _23_ of a given output controller if an individual setting doesn't exist already.

##### Use-cases

If you got a mix of different contactors or solenoids in your case, for example some smaller quick ones and some really heavy ones which need a longer tigger to fire correctly,
it makes no sense to adjust the global effect duration for **all** effects just to satisfy these heavy contactors because that will have a negative effect on your force feedback toys in general
Your setup will become more _sluggish_.
Using this tweak you can limit the modification to the ports where you need it.

#### turn_off

This tweak allows you to turn off specific ports of your output controller for specific games.
So `turn_off[18] = hs` turns off the port 18 for the game named _hs_.

##### Use-cases

The DOF Configtool only handles one _Beacon_. But a common setup is to have three of them with different colors:
* red
* orange/yellow
* blue

It makes no sense to run them all in parallel for any game. Therefore you should attach them to three different ports of your output controller and assign _Beacon_ to all of them in the DOF Configtool.
Using this tweak you can now turn them of individually. 
 
#### turn_on

This tweak allows you to turn on specific ports of your output controller **only** for specific games. It is the complement to `turn_off`.
So `turn_on[19] = f14,rs` turns on the port 19 **only** for the games named _f14_ and _rs_.

##### Use-cases

See the use-case for `turn_off`. In most cases these two tweaks need to be combined.

### Complete `tweaks.ini` example

```INI
[directoutputconfig40.ini]
; Set an effect duration of 100ms on device #40 ports #23 and #26
effect_duration[23] = 100
effect_duration[26] = 100

[directoutputconfig51.ini]
; Set an inverted effect duration of 500ms on device #51 port #11
effect_duration[11] = 500
; Turn off red beacon on device #51 port #18 for Road Show.
turn_off[18] = rs
; Turn on orange beacon on device #51 port #19 only for F14 and Road Show. (That
; implicitly disables the orange beacon for other games like High Speed.)
turn_on[19] = f14,rs
; Turn off blue beacon on device #51 port #20 for Road Show.
turn_off[20] = rs
```

### Usage

Just execute the DOF Configtool Client `tweak.php` script. It will download your individual config files and extract them to the directory as configured in `tweak.ini`.

Linux / macOS:
```
php tweak.php
```

Windows
```
php.exe tweak.php
```

