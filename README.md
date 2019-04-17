# DOF Configtool Client

This is (mainly) a client for downloading your config files from [http://configtool.vpuniverse.com] and for applying
additional tweaks to them. In addition it contains some tools to administer the Visual Pinball system itself.

## Motivation

The DOF Configtool is a great tool that covers most of the use-cases for the majority of it's users.
But DOF itself is more powerful. If you want to achieve special things, you can to that using the DOF Configtool by
adjusting the individual table settings. But doing so has to downsides:
* you might have to repeat such an adjustment for a lot or all games
* as soon as you have done that, you're decoupled from the upstream (the centralized table database) and you need to
  track changes manually

Both downsides could be avoided if we add more layers of configuration to the DOF Configtool, for example per port or
per toy. Since it's currently not possible to contribute to the DOF Configtool directly this client aims to add this
missing layers by introducing a kind of rule-set config file and a client that downloads your pre-configured
configuration files from the DOF Configtool and tweaks them accordingly.

### Notes

This client will never replace the awesome DOF Configtool!
But whenever you need to make an individual adjustment for _all_ tables for a specific port of an output controller,
this tool might become useful﻿ 😉

In addition to the available tweaks described below you can think of various other tweaks, for example inverting an
effect or anything else described at [http://directoutput.github.io/DirectOutput/inifiles.html].
Just open an issue at [https://github.com/mkalkbrenner/dof_configtool_client/issues] if you have an idea or require
something special.

The client is written in PHP because of the fact that the configtool itself is written in PHP.
That will hopefully ease an adoption of (some) features by the DOF configtool itself in the future.
The client itself very light wight. But due to the fact that PHP is uncommon for most Windows users, there two variants
to install the client: As a light wight PHP application as usual requiring a working PHP installation or as an
all-in-one package based on [https://github.com/cztomczak/phpdesktop](php-desktop).

Another advantage of this PHP based wev application approach is that you can use the tool remotely, for example using
your laptop and your preferred browser.

## Disclaimer

There's **no warrenty!**

If you use this software, you do it **on your own risk!**

## Installation

As mentioned above the client itself very light wight. But due to the fact that PHP is uncommon for most Windows users,
there two variants to install the client.

### Variant 1: Using a Windows Installer (mainly for users)

For every Release of the client there will be an all-in-one package based on
[https://github.com/cztomczak/phpdesktop] that could be installed on windows just like any windows
software.

### Variant 2: Regular PHP Stack (mainly for developers)

* Install PHP on your system. For Windows have a look at [https://windows.php.net/download/] or
  [http://php.net/manual/en/install.windows.php].
* Install composer on your system. Have a look at [https://getcomposer.org].
* Download the DOF Configtool Client from [https://github.com/mkalkbrenner/dof_configtool_client].
* Run `composer install` within the dof_configtool_client directory.
* Start the simple PHP web server within the dof_configtool_client directory: `php bin/console server:start`
* Open [http://localhost:8000] using your favorite browser.

## Usage

### 1. Configure

* Select `Settings` from the top menu.

* Complete your `Settings`:
  * You'll find your `LCP_APIKEY` on the start page after login into [http://configtool.vpuniverse.com]

  * As `DOF path` you have to provide the directory where DOF is installed. In most cases this should be
    `C:\DirectOutput\config`. **_Note:_** you have to ensure that this directory is writable. In case you get an error
    right click on the directory in Explorer in check this setting.

  * As `Visual Pinball path` you have to provide the directory where Visual Pinball is installed. In most cases this
    should be `C:\VisualPinball`. **_Note:_** you have to ensure that this directory is writable. In case you get an
    error right click on the directory in Explorer in check this setting.

### 1. Download your configs

* Select `Download` from the top menu.

* Download your configs using the corresponding button. (Note: Sometimes the configtool is slow. So the download could
  take some time.)

  **_Warning:_** Existing files will be overwritten.
 
### 2. Apply tweaks

**_Note:_** The tweaks will be applied to the files located in `DOF_CONFIG_PATH`. Therefore the download settings have
to be properly configured as mentioned above, no matter if you really download the files before tweaking them.

Select `Tweak` form the top menu. First of all you have to define your tweaks. To do so select `Edit tweak settings`and
continue as decribed in the next sections.

To apply your tweaks hit the corresponding button. That will tweak your existing configs according to your settings.
But before the tweaked configs will be finally saved you'll see a confirmation screen that indicates the resulting
changes. There you'll have to hit _Save_ to finally persist this changes.

**_Warning:_** The client doesn't detect if a config file has been _tweaked_ already. So executing the tweaks twice
without downloading the original configs before might have unwanted effects. You shoul always replace the config files
by a fresh download first, before applying new tweaks.
While absolute value tweaks like `effect_duration` should be kind of safe, multiplications like `adjust_intensity` will
be applied again on top of the previous tweak if you apply the tweaks twice.

**In general I suggest to use a tool like _git_ to track your config files and their tweaks, but maybe that might be
"too much" for the standard user** 😉

#### Scopes

Tweaks could be applied globally per output controller per port or individually per output controller per game per port.
In case that both - a global setting and an individual one for a specific game - exist, the individual one wins for that
game while the global one gets applied for all the other games.

##### Example:
```INI
[directoutputconfig40.ini]
; Global settings for output controller #40.
default_effect_duration[23] = 100
default_effect_duration[26] = 100
[abv106]
; Individual settings for game *abv106* for output controller #40.
default_effect_duration[23] = 230

[directoutputconfig51.ini]
; Global settings for output controller #51.
default_effect_duration[11] = 60
[abv106]
; Individual settings for game *abv106* for output controller #51.
default_effect_duration[13] = 120
```

* The effect duration for port _**26**_ on output controller _**40**_ will be set to _**100**ms_ for all games.
* The effect duration for port _**23**_ on output controller _**40**_ will be set to _**100**ms_ for all games except
  for game _**abv106**_ where the individual overwrite sets it to _**230**ms_.
* The effect duration for port _**11**_ on output controller _**51**_ will be set to _**60**ms_ for all games.
* The effect duration for port _**13**_ on output controller _**51**_ will be set to _**120**ms_ only for game
  _**abv106**_.

#### Available tweaks

##### `default_effect_duration`

The standard DOF configs define global and individual effect durations. Individual durations need to be set per game per
output controller port per trigger.

Using the DOF configtool it's impossible to set a different default duration other then the global one on a specific
port. You would have to do that for all tables.

Using this option you can set such a default duration per port for all games at once. But this will only happen if
there's not yet set an individual duration. To modify some of such individual durations see `target_effect_duration` and
`drop_target_effect_duration` below.

So `default_effect_duration[23] = 100` sets the duration to _100ms_ for all triggers on output _23_ of a given output
controller if an individual setting doesn't exist already.

###### Use-cases

If you got a mix of different contactors or solenoids in your cabinet, for example some smaller quick ones and some
really heavy ones which need a longer trigger to fire correctly, it makes no sense to adjust the global effect duration
for **all** effects just to satisfy these heavy contactors because that will have a negative effect on your force
feedback toys in general. Your setup will become more _sluggish_.
Using this tweak you can limit the modification to the ports where you need it.

##### `target_effect_duration` / `drop_target_effect_duration`

The standard DOF configs defines global effect durations for _targets_ and _drop targets_. Individual durations need to
be set per game per output controller port per trigger.

Using the DOF configtool it's impossible to set different durations other then the global ones on a specific port. You
would have to do that for all tables individually instead.

Using these options you can overwrite the global duration for _targets_ or _drop targets_ per port for all games at
once.

So `target_effect_duration[23] = 100` sets the duration of a target to _100ms_ for all triggers on output _23_ of a
given output controller if an individual setting doesn't exist already.

###### Use-cases

Same as for `default_effect_duration`.

##### `turn_off` / `turn_on`

The `turn_off` tweak allows you to turn off specific ports of your output controller for specific games.
So `turn_off[18] = hs` turns off the port 18 for the game named _hs_.

The `turn_on` tweak allows you to turn on specific ports of your output controller **only** for specific games. It is
the complement to `turn_off`. So `turn_on[19] = f14,rs` turns on the port 19 **only** for the games named _f14_ and
_rs_.

In most cases these two tweaks will be combined to keep the number of tweaks small.

###### Use-cases

The DOF Configtool only handles one _Beacon_. But a common setup is to have three of them with different colors:
* red
* orange/yellow
* blue

It most cases you won't run them all in parallel for any game. Therefore you should attach them to three different ports
of your output controller and assign _Beacon_ to all of them in the DOF Configtool.
Using this tweak you can now turn them of individually.

Given the three beacons example you now have the choice to configure _High Speed_ using the red beacon like used on the
original machine. Or create a nice police effect by turning on the blue and the red one but turn off the yellow one in
the middle.
But for _Road Show_ you might want to have the orange beacon to be the only one turned on.
For _F14 Tomcat_ you would keep all three active.

Another use-case is the handling of the heavy contactors we already covered in `default_effect_duration`. While these
are great for simulating bumbers their noice might be too much for other effects, for example the moving head on the
top left in _Monster Bash_. But instead of turning off that contactor entirely for that table you can mount small
contactors "in parallel" in your cabinet but attaching them to separate outputs. Now you can decide to use the heavy or
the small one depeneding on your game.
 
##### `adjust_intensity`

This tweak allows you to boost or reduce the intensity off an effect by a given factor on specific ports of your output
controller. So `adjust_intensity[28] = 1.2` will boost all existing intensities by _1.2_. For example an intensity of
_32_ will become _41_.

This tweak internally ensures that the intesity will not exceed the DOF maximum of 48.

In case of reducing the intensity by using a factor like _0.3_ the tweak will not reduce the intensity lower than _1_.

###### Use-cases

Maybe for safety reasons the maximum intensity for a shaker set be the DOF Configtool is _32_ of _48_.
In case you have a small shaker motor you might want to reach the maximum by boosting the default by _1.5_.

Since you have different effect levels within the same game, `adjust_intensity` uses a factor instead of absolute
numbers to keep these intensity ratio.

##### `merge`

This tweak merges two ports. In fact it appends the entire content of the second port to the first port. So
`merge[7] = 14` will append the entire configuration of port _14_ to port _7_. `merge[7] = 14,16` will append the entire
configurations of port _14_ and port _16_ to port _7_

###### Use-cases

In some way this tweak is comparable to _combos_ in the DOF Configtool. But beside the fact that a `merge` could be
applied to a specific game only, the main difference is, that `merge` is a kind of _inverted combo_. Instead of
combining two toys as one in general, you can replicate one toy on another toys and both are triggerd.

For example you might wish to turn on your beacon in addition when the fire button or the start button gets illuminated.
Or you would turn on the fan in addition to the shaker.

##### `merge_and_turn_off`

`merge_and_turn_off` basically works exactly like `merge` in `turn_off` in combination. so `merge_and_turn_off[7] = 14`
will append the entire configuration of port _14_ to port _7_ and then turn off port _7_ in general or a given game.

###### Use-cases

Similar to `merge` you can turn on a beacon but now _instead of_ the fire button or the start button.

##### `replace`

This tweak works like `merge` as it merges all given ports. But instead of appending their content to a port's
configuration they replace it. So `replace[7] = 14,16` will merge the configurations of port _14_ and _16_ and replace
port _7_ by it.

###### Use-cases

For example you can turn off the standard beacon effects by replacing them by the fire button. So the beacon will only
be turned on when the fire button gets illuminated.

##### `swap`

This tweak swaps to ports. So `swap[7] = 14` will assign the configuration of port _14_ to _7_ and vice versa.

###### Use-cases

Maybe you want to swap the gear and the shaker motor for a specific game, who knows? 😉

##### `move_drop_target`

Using this tweak you can extract the triggers for a drop target from a port (usually a bumper) and attach them to a
different port. So `move_drop_target[17] = 23` will remove all drop target instructions from port _17_ and attach them
to port _23_. `move_drop_target[17] = 23,36` will remove all drop target instructions from port _17_ and attach them to
the ports _23_ and _36_.

###### Use-cases

If you mount heavy contactors at the wall of your cabinet, they sound great for bumpers. But for drop targets the sound
effect might be too heavy. In this case you might want to move the drop target effects to smaller contactors or
contactors not mounted at the wall. The bumper effects themselves will not be touched and remain assigned where they
should be.

**_Note:_** These dedicated drop target contactors are for sure not listed in the DOF configtool itself. Once mounted to
your controller the DOF Configtool Client introduces them. Like for bumpers you can have up to 6 dedicated drop targets.
If you use combos instead of 6 dedicated bumpers, they apply 1:1 for the new drop targets.

##### `move_target`

This tweak is the complement to `move_drop_target` and enables you to extract the triggers for a target (usually a
bumper) from a port an attach them to a different port. So `move_target[17] = 23` will remove all target instructions
from port _17_ and attach them to port _23_.

###### Use-cases

See the use-cases for `move_drop_target` above.

##### `rgb_brightness`

The standard DOF Configtool allows to adjust the three brightness values (only globally for all tables and controllers
and their ports):
* PF Strobe MX
* Flasher
* Ledstrip Flasher

For other RGB toys there's no explicit setting at all.

Using this tweak you can set the brightness of any RGB toy (stripes, flasher, flipper buttons, ...) individually per
controller per port (and individually per table if you like). So `rgb_brightness[11] = 80` will set the brightness of
port _11_ to _80_. The brightness has to be set as hexadecimal value between _00_ and _FF_. So _FF_ means 100%
brightness, _80_ means 50%.

**_Note:_** A RGB toy is alway attached to three controller ports in a row. So the example above would automatically
adjust ports _12_ and _13_, too. So there's no need to provide dedicated settings for these two ports.

###### Use-cases

Maybe you want to have your addressable LED stripes mounted under the cabinet to have the full brightness while the
brightness of the playfield stripes should be reduced.

For example, if you configured the first three teensy driven stripes to be playfield left, top and right and the fourth
is the complex undercab illumination, your corresponding tweaks.ini section to set the playfield brightness to 50%
(hex 80 equals decimal 128, which is 50% of hex FF or decimal 256) and undercab to 80% (hex CD) will look like this:

```INI
[directoutputconfig30.ini]
rgb_brightness[1] = 80
rgb_brightness[4] = 80
rgb_brightness[7] = 80
rgb_brightness[10] = CD
```

#### Complete `tweaks.ini` example

```INI
[directoutputconfig40.ini]
; Set an effect duration of 100ms on device #40 ports #23 and #26.
default_effect_duration[23] = 100
default_effect_duration[26] = 100

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

### 3. RegEdit

The RegEdit feature isn't directly related to DOF config files. But VPinMame will store it's configurations in the
Windows Registry. If you ever ran into the situation that you have to change such a setting for all your games, you
might know uncomfortable that task is. 😉

* Select `RegEdit` from the top menu.
* Adjust the settings for all games at once.
* At the moment the moment the adjustable settings are limited for good reasons to ...
  * cabinet_mode
  * ignore_rom_crc
  * sound
  * samples
  * ddraw
  * showpindmd
  * showwindmd
  * dmd_colorize

### 4. Colorize

The DMD output of ROM files could be colorized. Depending on the ROM there're two different ways to do so. While one
just requires to put some files at the right place in the VPinMAME folder structure, the other requires to patch the
ROM binary, name it correctly and to put files at the right place in the VPinMAME folder structure.

The Colorize function provides a wizard that does everything for you and avoids that you have to deal with Windows
command line.

The first step is to select and check the color patch zip file you want to apply. Then you just need to follow the
instructions.

**_Note:_** In case that a ROM file needs to patched, the wizard starts `bspatch.exe`. So Windows might ask you to allow
it to apply modifications to the file system, which is required.

**_Note:_** `bspatch.exe` is already included in the DOF Configtool Client. There's no need to download it separately.

### 5. Backglasses

DirectB2S Backglasses need to match the VPX table name. So if you have several versions of a table you might need to
have multiple correctly named copies of a backglass. And if you use PUP Packs you need to delete a backglass or rename
it.

This simple tool eases such tasks as you can simply assign a backglass to a table or select to use a PUP Pack instead.
All the required file system tasks happen tranparently in the background.

In addition, the tool suggests backglasses for table.

### Leveraged components and their licences

* [https://symfony.com] MIT
* [https://github.com/sagebind/windows-registry] Apache 2.0
* [https://github.com/erusev/parsedown] MIT
* [https://github.com/iphis/fine-diff] MIT
* [https://getbootstrap.com] MIT
* [https://jquery.com] MIT
* [https://popper.js.org/] MIT
* [https://www.npmjs.com/package/bs-custom-file-input] MIT
* [https://github.com/cztomczak/phpdesktop] BSD 3-clause license
* [http://www.daemonology.net/bsdiff/] BSD Protection License

The _DOF Configtool Client_ source code is licenced under GPLv3.
