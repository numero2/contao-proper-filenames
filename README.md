Contao Proper Filenames
=======================

[![](https://img.shields.io/packagist/v/numero2/contao-proper-filenames.svg?style=flat-square)](https://packagist.org/packages/numero2/contao-proper-filenames) [![](https://img.shields.io/badge/License-LGPL%20v3-blue.svg?style=flat-square)](http://www.gnu.org/licenses/lgpl-3.0)

About
--
Sanitizes the filenames of files uploaded via the Contao file manager or Contao form. [Read more](https://www.numero2.de/contao/erweiterungen/proper-filenames.html)

System requirements
--

* [Contao 4.13 or newer](https://github.com/contao/contao)


Installation & Configuration
--

* Install via Contao Manager or Composer (`composer require numero2/contao-proper-filenames`)
* In the Backend go to `System Settings` and click `Check filenames` under `Upload settings`
* Configure how the filenames should be renamed by choosing an option from `Valid filename characters`


Commands
---

Recursively sanitize all files and folders in a given directory

```sh
contao-console contao:proper-filenames:sanitize myfolder -r
```

The extension only analyses files that are stored in Contao's DBFS (tl_files). The DBFS should be synchronised
before the call - either via the Dataimanager in the backend or with the following console call:

```sh
contao-console contao:filesync
```

To get a preview of how everything will be renamed there is also a `--dry-run` flag.
For all available flags and options see the help using `contao-console contao:proper-filenames:sanitize --help`.