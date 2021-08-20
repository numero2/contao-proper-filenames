Contao Proper Filenames
=======================

[![](https://img.shields.io/packagist/v/numero2/contao-proper-filenames.svg?style=flat-square)](https://packagist.org/packages/numero2/contao-proper-filenames) [![](https://img.shields.io/badge/License-LGPL%20v3-blue.svg?style=flat-square)](http://www.gnu.org/licenses/lgpl-3.0)

About
--
Sanitizes the filenames of files uploaded via the Contao File manager. [Read more](https://www.numero2.de/contao/erweiterungen/proper-filenames.html)

System requirements
--

* [Contao](https://github.com/contao/contao) 4.4 or newer


Installation & Configuration
--

* Install via Contao Manager or Composer (`composer require numero2/contao-proper-filenames`)
* In the Backend go to `System Settings` and click `Check filenames` under `Upload settings`
  * Configure how the filenames should be renamed by choosing an option from `Valid filename characters`
  * 💥 New in v.2.1.0: You can define a list of file extensions that will not be renamed automatically
  * 💥 New in v.2.1.0: While editing a folder in the `File manager` you can now choose to not rename files and folders located in a specific folder
