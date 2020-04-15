; Script generated by the Inno Setup Script Wizard.
; SEE THE DOCUMENTATION FOR DETAILS ON CREATING INNO SETUP SCRIPT FILES!

#define MyAppName "DOF Configtool Client"
#define MyAppVersion "0.6.5-beta.2"
#define MyAppPublisher "MK47"
#define MyAppURL "https://github.com/mkalkbrenner/dof_configtool_client"
#define MyAppExeName "DOFConfigtoolClient.exe"

[Setup]
; NOTE: The value of AppId uniquely identifies this application.
; Do not use the same AppId value in installers for other applications.
; (To generate a new GUID, click Tools | Generate GUID inside the IDE.)
AppId={{A7986BF0-448D-4A1F-BFE3-1796C28B2AFB}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
;AppVerName={#MyAppName} {#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName=C:\DOFConfigtoolClient
DisableDirPage=no
DisableProgramGroupPage=yes
LicenseFile=C:\Users\Markus\Documents\DOFConfigtoolClient\www\LICENSE
OutputBaseFilename=DOFConfigtoolClient-{#MyAppVersion}-Setup
Compression=lzma
SolidCompression=yes

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked

[Types]
Name: "full"; Description: "Full installation"
Name: "custom"; Description: "Custom installation"; Flags: iscustom

[Components]
Name: "program"; Description: "Program Files"; Types: full custom; Flags: fixed
Name: "git"; Description: "Embedded Git"; Types: full
Name: "bsdiff"; Description: "bsdiff/bspatch"; Types: full

[Files]
Source: "C:\Users\Markus\Documents\DOFConfigtoolClient\DOFConfigtoolClient.exe"; DestDir: "{app}"; Components: program; Flags: ignoreversion
Source: "C:\Users\Markus\Documents\DOFConfigtoolClient\*"; DestDir: "{app}"; Components: program; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: ".git,.gitignore,.github,.idea,dev.txt,composer.phar,www\ini,www\System*,www\build,www\var,www\public\ace\demo,www\public\ace\src,www\public\ace\src-min,phpunit,tests,webcache\*,\debug,*.log,directoutputconfig*.ini,DirectOutputShapes*,tablemappings*,PortableGit,bsdiff_win_exe,bin\console"
Source: "C:\Users\Markus\Documents\DOFConfigtoolClient\www\bin\PortableGit\*"; DestDir: "{app}\www\bin\PortableGit"; Components: git; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: ".git,.gitignore"
Source: "C:\Users\Markus\Documents\DOFConfigtoolClient\www\bin\bsdiff_win_exe\*"; DestDir: "{app}\www\bin\bsdiff_win_exe"; Components: bsdiff; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: ".git,.gitignore"
; NOTE: Don't use "Flags: ignoreversion" on any shared system files

[Icons]
Name: "{commonprograms}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"
Name: "{commondesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Flags: nowait postinstall skipifsilent

