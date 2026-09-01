<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2026, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenamesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;


class ProperFilenamesBundle extends Bundle {


    /**
     * {@inheritdoc}
     */
    public function getPath(): string {

        return \dirname(__DIR__);
    }
}
