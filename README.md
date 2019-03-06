# DOF Configtool Client

This is a client for downloading your config files from http://configtool.vpuniverse.com and for applying additional tweaks to them.

## Motivation

The DOF Configtool is a great tool that covers most of the use-cases for the majority of it's users.
But DOF itself is more powerful. If you want to achieve special things, you can to that using the DOF Configtool by adjusting the individual table settings.
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

## Disclaimer

There's **no warrenty!**

If you use this software, you do it **on your own risk!**

## 1. Download your configs

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
 
## 2. Apply tweaks

### Setup

Rename or copy `tweaks.ini.example` to `tweaks.ini` and adjust the file to your needs. See the next sections for the available tweaks and how they are applied.

**Note:** the tweaks will be applied to the files located at `DOF_CONFIG_PATH`. Therefore the `download.ini` has to be properly configured, no matter if you really download the files before tweaking them.

### Scopes

Tweaks could be applied globally per output controller per port or individually per output controller per game per port.
In case that a global setting exists and an individual one for a specific game, the individual one wins for that game while the global one gets applied for all the other games.

Example:
```INI
[directoutputconfig40.ini]
; Global settings for output controller #40.
effect_duration[23] = 100
effect_duration[26] = 100
[abv106]
; Individual settings for game *abv106* for output controller #40.
effect_duration[23] = 230

[directoutputconfig51.ini]
; Global settings for output controller #51.
effect_duration[11] = 60
[abv106]
; Individual settings for game *abv106* for output controller #51.
effect_duration[13] = 120
```

* The effect duration for port _**26**_ on output controller _**40**_ will be set to _**100**ms_ for all games.
* The effect duration for port _**23**_ on output controller _**40**_ will be set to _**100**ms_ for all games except for game _**abv106**_ where the individual overwrite sets it to _**230**ms_.
* The effect duration for port _**11**_ on output controller _**51**_ will be set to _**60**ms_ for all games.
* The effect duration for port _**13**_ on output controller _**51**_ will be set to _**120**ms_ only for game _**abv106**_.

### Available tweaks

#### `default_effect_duration`

The standard DOF configs define global and individual effect durations. Individual durations need to be set per game per output controller port per trigger.

Using the DOF configtool it's impossible to set a different default duration other then the global one on a specific port. You would have to do that for all tables.

Using this option you can set such an per port default duration for all games at once. But this will only happen if there's not yet set an individual duration.
So `default_effect_duration[23] = 100` sets the duration to _100ms_ for all triggers on output _23_ of a given output controller if an individual setting doesn't exist already.

##### Use-cases

If you got a mix of different contactors or solenoids in your case, for example some smaller quick ones and some really heavy ones which need a longer tigger to fire correctly,
it makes no sense to adjust the global effect duration for **all** effects just to satisfy these heavy contactors because that will have a negative effect on your force feedback toys in general
Your setup will become more _sluggish_.
Using this tweak you can limit the modification to the ports where you need it.

#### `turn_off`

This tweak allows you to turn off specific ports of your output controller for specific games.
So `turn_off[18] = hs` turns off the port 18 for the game named _hs_.

##### Use-cases

The DOF Configtool only handles one _Beacon_. But a common setup is to have three of them with different colors:
* red
* orange/yellow
* blue

It makes no sense to run them all in parallel for any game. Therefore you should attach them to three different ports of your output controller and assign _Beacon_ to all of them in the DOF Configtool.
Using this tweak you can now turn them of individually. 
 
#### `turn_on`

This tweak allows you to turn on specific ports of your output controller **only** for specific games. It is the complement to `turn_off`.
So `turn_on[19] = f14,rs` turns on the port 19 **only** for the games named _f14_ and _rs_.

##### Use-cases

See the use-case for `turn_off`. In most cases these two tweaks need to be combined.

#### `adjust_intensity`

This tweak allows you to boost or reduce the intensity off an effect by a given factor on specific ports of your output controller.
So `adjust_intensity[28] = 1.2` will boost all existing intensities by _1.2_. For example an instensity of _32_ will become _41_.
This tweak internally ensures that the intesity will not exceed the DOF maximum of 48.
In case of reducing the intensity by using a factor like _0.3_ the tweak will not reduce the intensity lower than _1_.

##### Use-cases

Maybe for safety reasons the maximum intensity for a shaker set be the DOF Configtool is _32_ of _48_.
In case you have a small shaker motor you might want to reach the maximum by boosting the default by _1.5_.
Since you have different effect levels within the same game, `adjust_intensity` uses a factor instead of absolute numbers to keep these intensity ratio.

### Complete `tweaks.ini` example

```INI
[directoutputconfig40.ini]
; Set an effect duration of 100ms on device #40 ports #23 and #26.
effect_duration[23] = 100
effect_duration[26] = 100

[directoutputconfig51.ini]
; Set an inverted effect duration of 500ms on device #51 port #11.
effect_duration[11] = 500
; Turn off red beacon on device #51 port #18 for Road Show.
turn_off[18] = rs
; Turn on orange beacon on device #51 port #19 only for F14 and Road Show. (That
; implicitly disables the orange beacon for other games like High Speed.)
turn_on[19] = f14,rs
; Turn off blue beacon on device #51 port #20 for Road Show.
turn_off[20] = rs
; Boost the intensity of the shaker motor on device #51 port #28 by factor 1.5.
adjust_intensity[28] = 1.5
[taf]
; Reduce the intensity of the shaker motor on device #51 port #28 by factor 0.7
; only for taf.
adjust_intensity[28] = 0.7
```

### Usage

Just execute the DOF Configtool Client `tweak.php` script. It will apply all your tweaks to your config files located in the directory as configured in `download.ini`.

Linux / macOS:
```
php tweak.php
```

Windows
```
php.exe tweak.php
```

**_Warning:_** The script doesn't detect if a config file has been _tweaked_ already. So running the script twice might have unwanted effects if you don't replace the config files by a fresh download first.
While absolute value tweaks like `effect_duration` should be kind of safe, multiplications like `adjust_intensity` will be applied again on top of the previous tweak.
I suggest to use a tool like _git_ to track your config files and their tweaks, but maybe that might be "too much" ;-)
