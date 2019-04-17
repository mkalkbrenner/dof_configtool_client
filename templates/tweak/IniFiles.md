## LedControl file numbering

Some types of output controllers support automatic discovery and configuration (e.g. LedWiz, PacLed64 and PacDrive). For those output controllers it is not necessary to have a cabinet config file and/or LedWizEquivalent toys configured.

When DOF doesn't find a entry in the cabinet config for a controller which support auto configuration it will add the necessary entries for the controller and the needed LedWizEquivalent toy to the config. The automatically created LedWizEquivalent toy all have a specific number which will be used to match a ini file with the same number. The following numbers are automatcally assign if a matching controller is found:

* __1-16__ is used for LedWiz units 1-16
* __19__ is used for the PacDrive
* __20-23__ is used for PacLed64 units 1-4

Output controllers which dont support auto config have to be defined in the cabinet config together with a matching LedWizEquivalent toy, to allow ini files to be applied to them. There is no forced number scheme for these, but it is recommend to use the following numbers since the config tool creates ini files with those numbers as well:

* __30-39__ for WS2811/WS2812 ledstrip controllers 1-10
* __40-49__ for SainSmart and other FT245RBitbang controllers.
* __100 and above__ for Artnet.

Files having a number, which does not match any LedWizEquivalent toy are ignored in the configuration process.


## Settings in DirectOutputConfig/LedControl ini files

The content of the ini files is a bit hard to read and understand. The following paragraphs try to explain the most importants points.

The ini files are  quite hard to edit manually. Therefore the best option to get your own settings, is to use the <a target="_blank" href="http://vpuniverse.com/ledwiz/login.php">DOF ConfigTool Website</a>. If you create your own settings, it is highly recommended that you use the \ref inifiles_testingapp "LedControlFileTester.exe" to check if your files can be parsed.

The first section in a `directoutputconfig.ini` file is the Colors section. It starts with the header `[Colors_DOF]` and a empty line following the header. After the header one or several colors are specified by a name and the brighness of 3 color components (red, green, blue) or 4 color components (red, green, blue, alpha). The values of the components have a range of 0 (off resp. fully transparent for alpha) to 48 (max brightness resp. fully opaque for alpha). For color specifications without a alpha component, alpha will be set to 0 (transparent) if all components are set to 0 (resulting in black). All other definitions will have a alpha value of 255 (fully opaque).
In addition it is also possible to specify the value for the 3 or 4 color components as hexvalues with a leading # (like color definition for html). When using hex values, the value range is 0-255 resp. 00 to FF in hexcode (e.g. #ff0000 is red or #00ff0080 is semitransparent blue).

A colors section might looks as follows:
~~~~~~~~~~~~~~~~~~~~~~~~~{.ini}
[Colors DOF]

Black=0,0,0
White=48,48,48,48
Red=48,0,0,255
Green=#0000ff
SemitransparentRed=48,0,0,24
SemitransparentBlue=#00ff0080
.. more color definitions ....
Brown=24,12,0
~~~~~~~~~~~~~~~~~~~~~~~~~

## Config DOF Section

The most important section in a directoutputconfig.ini file is the [Config DOF] section. It contains the effect definitions for the various tables.

Each line in this section contains the definition for a single table. The lines start with a short version of the romname of the table or a fake romname for EM tables. After the romname, there can be any number of columns (separated by commas) containing the settings for every output of a ledwizequivalent toy resp. the toys which use the outputs defined there.

Every column can contain any number of definitions how the framework should control the output. If more than one definition exists for a column, these definitions have to be separated by forward dashes (/).

The config section of a ini file might looks like this:
~~~~~~~~~~~~~~~~~~~~~~~~~{.ini}
[Config DOF]

abv106,S6/S7 60/S48,S4,S8/S11/W58 60/W59 60/W60 60/W61 60,S13,0,S1/S2/S46,S5/S12 60,0,S15/S16/S31 60/S32/W51 60/W52 60/W53 60,S14/S17 60/S30,S3,ON,ON,L34,W27 Blink,L3,S6 Yellow/S20 Yellow,S4 White/S14 Red/S21 Red/S22 Green/S27 Red/S28 Red/W62 Green/W65 Yellow/W66 Yellow/W69 Yellow,S1 Blue/S13 Red/W41 Yellow/W42 Yellow/W43 Yellow/W70 Yellow,S5 White/S15 Red/S24 Yellow/S25 Green/S27 Red/W54 White/W71 Yellow,S23 Yellow/S26 Yellow/W49 White/W50 White,0
afm,S48,S9,0,0,S3/S11,S1/S2/S46,S10,0,S4/S13,S12/S16,S7,ON,ON,0,L86,L88,S27 Red/S11 Red/S28 Green/W56 Green/W57 Green/W58 Green,S9 Blue/S25 Red/S26 Red/W43 Green,S12 Red/S21 Red/S23 Green,S10 Blue/S17 Red/S18 Red/S22 Yellow/W38 Yellow/W44 Green,S19 Red/S13 Red/S20 Green/W41 Green/W42 Green/W48 Yellow,S5 600 I32/S6 600 I32/S8 600 I32/S14 600 I32/S15 600 I32
... more table configs ...
atlantis,S48,S7,0,S2/S9/W1 60/W2 60/W3 60/W4 60,S4/S11,S12/S14/S46,S8,0,S1/S5,S6,S15,ON,ON,0,W16 Blink,ON,S4 Red/W25 Red/W26 White/W27 Yellow/L43 Orange,S7 Yellow/W32 Blue/L77 White,S6 Red/W12 White/W33 Red/W34 White/W35 Yellow/L45 Red,S8 Yellow/W36 Cyan/L62 White,S5 Red/W13 White/W28 Red/W29 White/W30 Yellow/W45 Blue/L76 Orange,0
~~~~~~~~~~~~~~~~~~~~~~~~~


## Trigger parameters

The first part of a setting defines how the setting/effect is triggered and must always be one of the following:

* __TableElementTypeChar plus Number__ (e.g. S48 for solenoid 48) determines which table element is controlling the specified effect.
* __$TableElementName__ (e.g. $Quit) defines the name of the table element which is controlling the output.
* __List of TableElementTypeChars plus Numbers__ or __$TableElementNames__ delimited by | (e.g. S48|W12|L59|$Quit). This setting assigns the same effect to all table elements in the list. 
* __Condition__ which controlles wether the effect is triggered or not. Conditions must always be in brackets. Example: (S48=1 and W29=0 and (L59=1 or L43<>0)). For more details regarding the the expression language for conditions please read: http://flee.codeplex.com/wikipage?title=LanguageReference
* __On__ resp. __1__ turns the specified effect constantly on.
* __B__ defines a static (not externaly controlled) blinking.

## General parameters

The second and following parts of a setting can contain one or several of the following paramaters:

* __Color name__ as specified in the colors section of the file. Only valid as the second value (e.g. S48 Blue).
* __Hex color definition__ (e.g. #ff0000ff for opaque red). Take note that these color definitions allow for values from 0-255 in contrast to the colors section which only support 0-48. Hex color definitions can contain 3 or 4 parts (without or with alpha value). Setting is only accepted as the second value.
* __Blink__ defines blinking with a default interval of 500ms.
* __I{Number}__ defines a intensity/level of the output. The number can either be specified as a decmal number between 0 (e.g. I0 for off) and 48 (e.g. I48 for fully on) or as a hexadecimal number between 00 (off) and FF (fully on) with a leading # (e.g. I#80 for 50% power). This settings does only have a effect for settings without a color definition.
* __L{Number}__ defines the layer on which the setting operates. In most cases the setting is not required, since DOF will assign ascending layer numbers to the settings for a column anyway.
* __W{NumberOfMilliseconds}__ defines a wait period resp. delay before the effect executes after it has been triggered.
* __M{NumberOfMilliseconds}__ defines the minimum duration for the effect in milliseconds.
* __Max{NummberOfMilliseconds}__ defines the maximum duration for the effect in milliseconds.
* __F{NumberOfMilliseconds}, FU{NumberOfMilliseconds}, FD{NumberOfMilliseconds}__ are used to specify the fading duration in milliseconds. _F_ sets the duration for both fading up and down, _FU_ controls fading up only and _FD_ fading down only.
* __E{NumberOfMilliseconds}__ specifies a extended duration in milliseconds for the effect (after it has been turned off).
* __BL{Number}__ specifies the value of the blink effect during the low period of the blinking (High value=trigger value of the effect, typicaly 255).  The number can either be specified as a decmal number between 0 and 48 (e.g. BL3) or as a hexadecimal number between 00 and FF with a leading # (e.g. BL#30).
* __BPW{Percentage}__ defines the blink pulse width in percent of the blink interval. Valid values are 1-99, default value if not defined is 50.
* __BNP{NumberOfMilliseconds}__ defines the interval (duration of one on/off period) for nested blinking. This allows to define a second level of blinking within the _on_ period of the normal blinking.
* __BNPW{Percentage}__ defines the blink pulse width for nested blinking in percent of the blink interval.
* __Invert__ inverts the effect, so the effect will be active when it is normaly inactive and vice versa.
* __NoBool__ indicates that the trigger value off the effect is not to be treated as a boolean value resp. that the daufault mapping of the value to 0 or 255 (255 for all values which are not 0) should not take place.
* __Numeric Values__ without any extra character can be used to specify the duration of the effect or the blinking behaviour. If blinking has been defined (BLINK para) and one numeric value has been specified, the numeric value defines the blink interval. If two numeric values are specified, the first numeric value defines the duration of the effect and the second numeric value defines the number of blinks during the defined duration. If no blink para and only one numeric value is defined, the numeric value defines the duration of the effect in milliseconds.

## Matrix/area effect parameters

For adressable ledstrips and other toys which implement the IMatrixToy interface the following extra parameters can be used to control the hardware referenced by the matrix. For settings controlling a matrix you have to use at least one of these paras, so DOF realizes that a matrix/area is to be controlled.

The matrix effects and parameters can be combined with the general paras mentioned above.

### General Matrix Paras

The following 4 paramaters are specifying the area of a matrix which is to be influenced by a matrix effect:
* __AL{LeftPosition}__ defines the left of the upper left corner of the area of the matrix which is to be controlled by the effect. Position is expressed in percent of the matrix width (0-100).
* __AT{TopPosition}__ defines the upper part of the upper left corner of the area of the matrix which is to be controlled by the effect. Position is expressed in percent of the matrix height (0-100).
* __AW{Width}__ defines the width of the area of the matrix which is to be controlled by the effect. Width is expressed in percent of the matrix width (0-100).
* __AH{Height}__ defines the height of the area of the matrix which is to be controlled by the effect. Height is expressed in percent of the matrix height (0-100).

### Shift Effect Paras

The matrix shift effect moves a color/value with a defineable direction, speed and acceleration through the matrix:
* __ASD{DirectionCharacter}__ defines the direction for the ColorShiftEffect. Valid directions are: R-Right, L-Left, U-Up, D-Down.
* __ASS{Speed}__ defines the speed for the ColorShiftEffect expressed in percent of the effect area per second. 100 will shift through the effect area in one second, 400 will shift through the effect area in 1/4 second. Min. speed is 1, max. speed is 10000.
* __ASS{Speed}MS__ defines the time in milliseconds the color needs to shift throgh the effect area. Min duration is 10ms, max duration is 100000ms.
* __ASA{Acceleration}__ defines the acceleration for the ColorShiftEffect, expressed in percent of the effect area per second. Acceleration can be positive (speed increases) or negative (speed decreases). Speed will never decrease below 1 and never increase above 10000.

### Flicker Effect Paras

The flicker effect generates random flickering with a defineable density and duration for the single flickers:
* __AFDEN{Percentage}__ defines the density for the flicker effect. Density is expressed in percent and has a valid value range of 1 to 99.
* __AFMIN{DurationInMilliseconds}__ defines the min duration for the flicker of a single led in milliseconds.
* __AFMAX{DurationInMilliseconds}__ defines the max duration for the flicker of a single led in milliseconds.
* __AFFADE{DurationInMilliseconds}__ defines the the duration of the fading for the flickering elements.

### Plasma Effect Parameter

### Shape Effect Parameters

The frameork is able to display shapes, which are definied in the DirectOutputShapes.xml file, on a matrix toy. The area which is occupied by the shape is defined with the usual area parameters (AL?, AT?, AW?, AH?). DOF supports static and animated shapes (all defined in the mentioned xml file).
Shapes can be displayed in any color. Just specify the color as you would for other effects.

There is only one parameter which is specific to the shape effect:
* __SHP{ShapeName}__ defines the named of the shape to be displayed. Check the DirectOutputShapes.xml file in the config directory for valid shape names (you can also extend this file if you like). 

### Bitmap Effect Paras

DOF can display a part of a bitmap image on a matrix toy. The defined part of the bitmap is scaled to the size of the matrix, so the actual resolution of the matrix does not matter.
If you specify a bitmap effect by using one of the following parameters, DOF will try to load a bitmap image (gif, png, jpg and more should all work) from the same directory as the ini file. The bitmap image has to be named like the short rom name in the first collumn of the ini file (e.g. mm.png for Medival Madness or afm.gif for Attack from Mars).
* __ABL{LeftPostionInPixels}__ defines the left/horizontal part of the upper left corner of the part of the bitmap to be displayed. Defaults to 0 if not specified.
* __ABW{WidthInPixels}__ defines the width of the part of the bitmap to be displayed. Defaults to the total width of the image if not specified.
* __ABT{TopPositionInPixels}__ defines the upper/vertical part of the upper left corner of the part of the bitmap to be displayed. Defaults to 0 if not specified.
* __ABH{HeightInPixels}__ specifies the height of the part of the bitmap to be displayed. Defaults to the total height of the image if not specified.
* __ABF{FrameNumber}__ indicates the frame of the image to be displayed. This setting is only relevant if you use animated gif images. Defaults to the first frame of the animated gif if not specified.

### Bitmap Animation Paras
The following extra paras can be used in addition to the bitmap paras to animate the bitmap display on the matrix:
* __AAC{CountOfFrames}__ specifies the total number of frames of the animation.
* __AAF{FramesPerSecond}__ specifies the number of frames per second. 
* __AAD{FrameExtractionDirectionCharacter}__ defines the direction in which DOF moves through the source image to generate the animation. Valid values for the direction character are L=Left, D=Down, F=Frame (for animated gifs)
* __AAS{FrameExtrationStepSize}__ defines the size of the steps DOF takes to move through the source image to generate the animation. The step size is either in pixels or in frames.
* __AAB{AnimationBehaviourCharacter}__ defines the behaviour when the animation is triggered. Valid values are O=show animation once, L=Start at beginning and show animation in a loop (default), C=Continue at last position and show animation in a loop

The following image might give a a better idea what these parameters do. It shows the behaviour for a setting like S48 AL0 AT10 AW100 AH20 AAC116 AADD AAS5 AAF30 AABL.

https://raw.githubusercontent.com/DirectOutput/DirectOutput/master/Documentation/img/RGBAMatrixBitmapAnimationEffectExample.png


## Setting examples

Here are a few typical settings which are used for toys like solenoids or RGB leds:

* __S48__ will turn the toy associated with the column on and off depending on the state of solenoid 48.
* __S48 Green__ will set the rgb led associated with the column of the file to green depending on the state of solenoid 48.
* __S48 Green Blink__ will set the rgb led associated with the column of the file to green blinking depending on the state of solenoid 48.
* __W32 Red 2500 5__ will make a rgbled blink red for 5 times within a duration of 2500ms when switch 32 is activated.
* __W32 Red 2500 5 F200__ same result as previous example but the color will fadin and out in 200 millieconds.
* __W36 I32__ sets the output associated with the column to intensity 32 as long as switch 32 is active.
