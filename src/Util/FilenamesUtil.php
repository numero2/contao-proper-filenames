<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenamesBundle\Util;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Config;
use Contao\DataContainer;
use Contao\DC_Folder;
use Contao\Files as CoreFiles;
use numero2\ProperFilenamesBundle\EventListener\DataContainer\FilesListener;


class FilenamesUtil {


    /**
     * Sanitizes the given file- or foldername
     *
     * @param string $strName
     * @param Contao\DataContainer|array $dc
     *
     * @return string
     */
    public static function sanitizeFileOrFolderName( $strName, $dc=null ): string {

        if( !Config::get('checkFilenames') || !Config::get('filenameValidCharacters') ) {
            return $strName;
        }

        if( self::skipSanitize($strName, $dc) ) {
            return $strName;
        }

        // allow slashes when creating new folders
        if( $dc instanceof DC_Folder && $dc->table === "tl_files" && $dc->field === "name" ) {

            $aChunks = array_filter(explode(DIRECTORY_SEPARATOR, $strName));

            // sanitize each chunk
            if( $dc->value === '__new__' && count($aChunks) > 1 ) {

                $aNewChunks = [];

                foreach( $aChunks as $chunk ) {
                    $aNewChunks[] = self::sanitizeFileOrFolderName($chunk, $dc);
                }

                $newName = implode(DIRECTORY_SEPARATOR, $aNewChunks);

                return $newName;
            }
        }

        $newName = $strName;

        // remove forbidden characters
        $newName = (new SlugGenerator(self::getSlugOptions()))->generate($newName);

        // replace double underscores
        $newName = self::replaceUnderscores($newName);

        // cut name to length
        if( !Config::get('doNotTrimFilenames') ) {
            $newName = substr( $newName, 0, 32 );
        }

        return $newName;
    }


    /**
     * Replaces doubled underscores in the given filename
     *
     * @param string $strFile
     *
     * @return string
     */
    public static function replaceUnderscores( $strFile ): string {

        $newFilename = str_replace( "__", "_", $strFile );

        if( $newFilename != $strFile ) {
            $newFilename = self::replaceUnderscores($newFilename);
        }

        return $newFilename;
    }


    /**
     * Check if the given file should not be sanitized
     *
     * @param string $name
     * @param Contao\DataContainer|array $dc
     *
     * @return bool
     */
    public static function skipSanitize( $name, $dc ): bool {

        // check if file should be ignored by its extension
        // new upload
        if( is_array($dc) && !empty($dc['extension']) && in_array($dc['extension'], explode(',', Config::get('excludeFileExtensions'))) ) {
            return true;
        }

        // rename in BE
        if( $dc instanceof DataContainer && !empty($dc->activeRecord->extension) && in_array($dc->activeRecord->extension, explode(',', Config::get('excludeFileExtensions'))) ) {
            return true;
        }

        // check if a parent folder is set to not sanitize
        $objDc = new \stdClass();

        // new upload
        if( is_array($dc) && !empty($dc['dirname']) ) {
            $objDc->id = $dc['dirname'];
        }
        // rename in BE
        if( $dc instanceof DataContainer && is_string($dc->id) ) {
            $objDc->id = $dc->id;
        }

        if( $objDc->id ?? null ) {
            if( FilesListener::checkParentFolder('', $objDc) ) {
                return true;
            }
        }

        return false;
    }


    /**
     * Return the Slug options
     *
     * @return array The slug options
     */
    public static function getSlugOptions(): array {

        $slugOptions = [];

        if( $validChars = Config::get('filenameValidCharacters') ) {
            $slugOptions['validChars'] = $validChars;
        }

        if( $locale = Config::get('filenameValidCharactersLocale') ) {
            $slugOptions['locale'] = $locale;
        }

        $slugOptions['validChars'] .= '_';

        return $slugOptions;
    }
}
