## Installation

As mentioned above, the client itself very light wight. But due to the fact that PHP is uncommon for most Windows users,
there two variants to install the client.

### Variant 1: Using a Windows Installer (mainly for users)

For every Release of the client there will be an all-in-one package based on [https://github.com/cztomczak/phpdesktop]
that could be installed on windows just like any windows software.

### Variant 2: Regular PHP Stack (mainly for developers)

* Install PHP on your system. For Windows have a look at [https://windows.php.net/download/] or
  [http://php.net/manual/en/install.windows.php].
* Install composer on your system. Have a look at [https://getcomposer.org].
* Download the DOF Configtool Client from [https://github.com/mkalkbrenner/dof_configtool_client] or clone it via git.
* Run `composer install` within the dof_configtool_client directory.
* Run `composer require symfony/web-server-bundle`
* Start the simple PHP web server within the dof_configtool_client directory: `php bin/console server:start`
* Open [http://localhost:8000] using your favorite browser.
