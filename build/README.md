Set Version number in DOFConfigtoolClient.iss, console.php and index.php

Unix:
```
composer update --ignore-platform-reqs
```
Windows:
```
C:\Users\Markus\Documents\DOFConfigtoolClient\www>..\php\php composer.phar update
```

Disable remote access!

Portable git:
https://git-scm.com/download/win
32-bit Git for Windows Portable
(2.30.0)
=> bin/PortableGit

PHP:
https://windows.php.net/download
VC15 x86 Thread Safe (2020-Nov-24 15:09:00)
=> ../php

APCu causes issues on parallel requests!
https://pecl.php.net/package/APCu
7.3 Thread Safe (NTS) x86

https://github.com/ajaxorg/ace-builds/releases
(v1.4.7)
=> public/ace