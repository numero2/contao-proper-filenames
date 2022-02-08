<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2021 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright 2021 numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenames;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\Config;
use Contao\Database;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use numero2\ProperFilenames\DCAHelper\Files;


class CheckFilenames extends \Frontend {


    /**
     * Renames the given files
     *
     * @param array $arrFiles
     * @param bool $addSuffix
     *
     * @return array
     */
    public function renameFiles( $arrFiles, $addSuffix=false ) {

        $aRenamed = [];

        if( !Config::get('checkFilenames') ) {
            return $aRenamed;
        }

        $this->import('Files');

        if( !empty($arrFiles) ) {

            foreach( $arrFiles as $file ) {

                $info = pathinfo($file);

                $oldFileName = $info['filename'] . '.' . strtolower($info['extension']);
                $newFileName = self::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                // rename physical file
                if( $oldFileName !== $newFileName ) {

                    $newFile = $info['dirname'] . '/' . $newFileName;

                    // add numerical suffix to prevent overwriting of already existing files with the same name
                    if( $addSuffix && file_exists(TL_ROOT . '/' . $newFile) ) {

                        $newInfo = pathinfo($newFile);

                        $offset = 1;

                        $arrAll = scan(TL_ROOT . '/' . $info['dirname']);
                        $arrExistingFiles = preg_grep('/^' . preg_quote($newInfo['filename'], '/') . '.*\.' . preg_quote($info['extension'], '/') . '/', $arrAll);

                        foreach( $arrExistingFiles as $strFile ) {

                            if( preg_match('/__[0-9]+\.' . preg_quote($info['extension'], '/') . '$/', $strFile) ) {

                                $strFile = str_replace('.' . $info['extension'], '', $strFile);
                                $intValue = (int) substr($strFile, (strrpos($strFile, '_') + 1));

                                $offset = max($offset, $intValue);
                            }
                        }

                        $newFile = str_replace($newInfo['filename'], $newInfo['filename'] . '__' . ++$offset, $newFile);
                    }

                    $aRenamed[$file] = $newFile;

                    // create a temp file because the \Files class can't handle proper renaming on windows
                    $this->Files->rename($file, $newFile.'.tmp');
                    $this->Files->rename($newFile.'.tmp', $newFile);

                    // rename file in database
                    $objFile = FilesModel::findByPath($file);
                    $objFile->path = $newFile;
                    $objFile->hash = md5_file(TL_ROOT . '/' . $newFile);
                    $objFile->name = $newFileName;

                    if( $objFile->save() && TL_MODE === 'BE' ) {

                        Message::addInfo(sprintf(
                            $GLOBALS['TL_LANG']['MSC']['proper_filenames_renamed']
                        ,   $oldFileName
                        ,   $newFileName
                        ));
                    }
                }
            }
        }

        return $aRenamed;
    }


    /**
     * Rename an uploaded file
     *
     * @param Contao\Widget $objWidget
     * @param string $formId
     * @param array $arrData
     * @param Contao\Form $objForm
     *
     * @return Contao\Widget
     */
    public function renameFormUploads( $objWidget, $formId, $formData, $form ) {

        if( Input::post('FORM_SUBMIT') == $formId ) {

            if( $objWidget->storeFile && !empty($_FILES[$objWidget->name]) && $_FILES[$objWidget->name]['error'] === 0 && !$objWidget->doNotSanitize ) {

                $info = pathinfo($_FILES[$objWidget->name]['name']);
                $newFileName = self::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                $_FILES[$objWidget->name]['name'] = $newFileName;
            }
        }


        return $objWidget;
    }


    /**
     * Sanitizes the given file- or foldername
     *
     * @param string $strName
     * @param Contao\DataContainer|array $dc
     *
     * @return string
     */
    public static function sanitizeFileOrFolderName( $strName, $dc=null ) {

        if( !Config::get('checkFilenames') ) {
            return $strName;
        }

        if( self::skipSanitize($strName, $dc) ) {
            return $strName;
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
    protected static function replaceUnderscores( $strFile ) {

        $newFilename = str_replace( "__", "_", $strFile );

        if( $newFilename != $strFile ) {
            $newFilename = self::replaceUnderscores( $newFilename );
        }

        return $newFilename;
    }


    /**
     * check if the given file should not be sanitized
     *
     * @param string $name
     * @param Contao\DataContainer|array $dc
     *
     * @return bool
     */
    protected static function skipSanitize( $name, $dc ) {

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

        if( $objDc->id ) {
            if( Files::checkParentFolder('', $objDc) ) {
                return true;
            }
        }

        return false;
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
