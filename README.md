Contao Proper Filenames
=======================

About
--
Santizes the filenames of files uploaded via the Contao File manager.

System requirements
--

* [Contao](https://github.com/contao/core) 3.2.5 or newer  / successfully tested with Contao 4.3.9


Installation & Configuration
--

* Create a folder named `proper-filenames` in `system/modules`
* Clone this repository into the new folder
* In the Backend go to System Settings and click `Check filenames` under `Upload settings`

**Additional step for Contao 4.X:**
Open `app/AppKernel.php` and add the following line to the $bundles array
```php
new Contao\CoreBundle\HttpKernel\Bundle\ContaoModuleBundle('proper-filenames', $this->getRootDir())
```