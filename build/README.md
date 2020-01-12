Set Version number in DOFConfigtoolClient.iss and index.php

```
composer update --ignore-platform-reqs --no-dev
```

Portable git:
https://git-scm.com/download/win
32-bit Git for Windows Portable
(2.24.1)
=> bin/PortableGit

PHP:
https://windows.php.net/download
VC15 x86 Thread Safe (2019-Dec-17 15:43:37)
=> ../php

APCu causes issues on parallel requests!
https://pecl.php.net/package/APCu
7.3 Thread Safe (NTS) x86

https://github.com/ajaxorg/ace-builds/releases
(v1.4.7)
=> public/ace