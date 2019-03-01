# DOF Configtool Client

This is a client for downloading your config files from http://configtool.vpuniverse.com and for applying additional tweaks to them.

## Setup

You need to install PHP on your system. For Windows have a look at http://php.net/manual/en/install.windows.php

Download the DOF Configtool Client from https://github.com/mkalkbrenner/dof_configtool_client

Rename or copy `download.ini.example` to `download.ini` and adjust the file to your needs:
 * Add your API Key which you obtain from the DOF Configtool Account Settings as `LCP_APIKEY`
 * Adjust `DOF_CONFIG_PATH` to your needs. The downloaded config files will be extracted into this directory.

 
## Usage

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
 
## Tweaks

### Setup

Rename or copy `tweaks.ini.example` to `tweaks.ini` and adjust the file to your needs. See the next section for the available tweaks.

### Available tweaks

 * **effect_duration**:
   The standard DOF configs define global and individual effect durations. Individual durations need to be set per output controller per table per trigger per output.
   Using this option you can set such an individual duration for all tables:
   `effect_duration[23] = 100` sets the duration to _100ms_ for all triggers on output _23_ of a given output controller if an individual setting doesn't exist already.
   This setting is useful for heavy contactors/solenoids that need a longer tigger to fire correctly.

 * to be continued ...
  
### Complete `tweaks.ini` example

```
[directoutputconfig40.ini]
; Set an effect duration of 100ms on device #40 ports #23 and #26
effect_duration[23] = 100
effect_duration[26] = 100

[directoutputconfig51.ini]
; Set an inverted effect duration of 500ms on device #51 port #11
effect_duration[11] = 500
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

## Notes

This client won't replace the awesome DOF Configtool!
But whenever you need to make an individual adjustment for ALL tables for a specific port of an output controller, this tool might become useful﻿ 😉

In addition to the available tweaks above you can think of various other tweaks,
for example inverting an effect or anything else described at http://directoutput.github.io/DirectOutput/inifiles.html
Just open an issue if yopu have an idea or require something special.

BTW the client is written in PHP because of the fact that the configtool itself is written in PHP.
And I want to ease an adoption of features by the configtool itself.

