<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2020 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright 2020 numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenames;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Config;
use Contao\CoreBundle\Slug\ValidCharacters;
use Contao\FilesModel;
use Contao\Message;
use Contao\System;


class CheckFilenames extends \Frontend {


    /**
     * Renames the given files
     *
     * @param array $arrFiles
     *
     * @return none
     */
    public function renameFiles( $arrFiles ) {

        if( !Config::get('checkFilenames') )
            return null;

        $this->Import('Files');
        $this->Import('FilesModel');

        if( !empty($arrFiles) ) {

            foreach( $arrFiles as $file ) {

                $info = pathinfo($file);

                $oldFileName = $info['filename'] . '.' . strtolower($info['extension']);
                $newFileName = self::sanitizeFileOrFolderName($info['filename']) . '.' . strtolower($info['extension']);

                // rename physical file
                if( $oldFileName !== $newFileName ) {

                    $newFile = $info['dirname'] . '/' . $newFileName;

                    // create a temp file because the \Files class can't handle proper renaming on windows
                    $this->Files->rename($file, $newFile.'.tmp');
                    $this->Files->rename($newFile.'.tmp', $newFile);

                    // rename file in database
                    $objFile = FilesModel::findByPath($file);
                    $objFile->path = $newFile;
                    $objFile->hash = md5_file(TL_ROOT . '/' . $newFile);
                    $objFile->name = $newFileName;

                    if( $objFile->save() ) {

                        Message::addInfo(sprintf(
                            $GLOBALS['TL_LANG']['MSC']['proper_filenames_renamed']
                        ,   $oldFileName
                        ,   $newFileName
                        ));
                    }
                }
            }
        }
    }


    /**
     * Sanitizes the given file- or foldername
     *
     * @param  string $strName
     *
     * @return string
     */
    public static function sanitizeFileOrFolderName( $strName ) {

        if( !Config::get('checkFilenames') )
            return $strName;

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
     * Provides the options for tl_settings.filenameValidCharacters
     *
     * @return array
     */
    public function getValidCharacterOptions() {

        if( class_exists(ValidCharacters::class) ) {
            return System::getContainer()->get('contao.slug.valid_characters')->getOptions();
        }

        return array(
            '\pN\p{Ll}' => 'unicodeLowercase',
            '\pN\pL' => 'unicode',
            '0-9a-z' => 'asciiLowercase',
            '0-9a-zA-Z' => 'ascii'
        );
    }


    /**
     * Replaces doubled underscores in the given filename
     *
     * @param string $strFile
     *
     * @return string
     */
    protected static function replaceUnderscores( $strFile ) {

        $newFilename = str_replace( "__", "_", $strFile );

        if( $newFilename != $strFile ) {
            $newFilename = self::replaceUnderscores( $newFilename );
        }

        return $newFilename;
    }


    /**
     * Return the slug options
     *
     * @return array The slug options
     */
    protected static function getSlugOptions() {

        $slugOptions = array();

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
