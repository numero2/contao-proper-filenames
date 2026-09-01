<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2026, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenamesBundle\EventListener\Hooks;

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Files as CoreFiles;
use Contao\FilesModel;
use Contao\Form;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Contao\Widget;
use numero2\ProperFilenamesBundle\Util\FilenamesUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class CheckFilenamesListener {


    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    private RouterInterface $router;

    /**
     * @var Contao\CoreBundle\Routing\ScopeMatcher
     */
    private ScopeMatcher $scopeMatcher;

    /**
     * @var Symfony\Contracts\Translation\TranslatorInterface
     */
    private TranslatorInterface $translator;


    public function __construct( RequestStack $requestStack, RouterInterface $router, ScopeMatcher $scopeMatcher, TranslatorInterface $translator ) {

        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->scopeMatcher = $scopeMatcher;
        $this->translator = $translator;
    }


    /**
     * Renames uploaded files (backend)
     *
     * @param array $arrFiles
     */
    #[AsHook('postUpload')]
    public function renameFilesBackend( array &$arrFiles ): void {

        $aRenamed = [];

        if( !Config::get('checkFilenames') ) {
            return;
        }

        if( !empty($arrFiles) ) {

            $oFiles = null;
            $oFiles = CoreFiles::getInstance();

            foreach( $arrFiles as $i => $file ) {

                $info = pathinfo($file);

                $oldFileName = $info['filename'] . '.' . strtolower($info['extension']);
                $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                // rename physical file
                if( $oldFileName !== $newFileName ) {

                    $newFile = $info['dirname'] . '/' . $newFileName;

                    $aRenamed[$file] = $newFile;

                    $oFiles->rename($file, $newFile);

                    // get the database entry created by Contao
                    $objFile = FilesModel::findByPath($file);

                    // check if file already exists under the new name
                    if( FilesModel::findByPath($newFile) ) {

                        // delete old file in database
                        $objFile->delete();

                    } else {

                        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

                        // rename file in database
                        $objFile->path = $newFile;
                        $objFile->hash = md5_file($rootDir . '/' . $newFile);
                        $objFile->name = $newFileName;

                        if( $objFile->save() && $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest()) ) {

                            Message::addInfo(sprintf(
                                $GLOBALS['TL_LANG']['MSC']['proper_filenames_renamed']
                            ,   $oldFileName
                            ,   $newFileName
                            ));

                            // write back new filename for use in further hooks
                            $arrFiles[$i] = $newFile;
                        }
                    }
                }
            }
        }
    }


    /**
     * Renames an uploaded file (frontend)
     *
     * @param Contao\Widget $objWidget
     * @param string $formId
     * @param array $arrData
     * @param Contao\Form $objForm
     *
     * @return Contao\Widget
     */
    #[AsHook('loadFormField')]
    public function renameFileUpload( Widget $objWidget, string $formId, array $formData, Form $form ): Widget {

        if( Input::post('FORM_SUBMIT') == $formId ) {

            $widgetName = $objWidget->name;

            if( $objWidget->multipleFiles ) {
                $widgetName = substr($widgetName,0,-2);
            }

            if( $objWidget->storeFile && !empty($_FILES[$widgetName]) && !$objWidget->doNotSanitize ) {

                // multi-upload
                if( $objWidget->multipleFiles ) {

                    foreach( $_FILES[$widgetName]['name'] as $i => $v ) {

                        if( $_FILES[$widgetName]['error'][$i] === 0 ) {

                            $info = pathinfo($v);
                            $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                            $_FILES[$widgetName]['name'][$i] = $newFileName;
                        }
                    }

                // single upload
                } else {

                    if( $_FILES[$widgetName]['error'] === 0 ) {

                        $info = pathinfo($_FILES[$widgetName]['name']);
                        $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                        $_FILES[$widgetName]['name'] = $newFileName;
                    }
                }
            }
        }

        return $objWidget;
    }


    /**
     * Checks if the renaming of files is activated but missing settings
     *
     * @return string
     */
    #[AsHook('getSystemMessages')]
    public function checkMissingSettings(): string {

        if( !Config::get('checkFilenames') || Config::get('checkFilenames') && Config::get('filenameValidCharacters') ) {
            return '';
        }

        $msg = sprintf(
            $this->translator->trans('ERR.proper_filenames_not_configured', [], 'contao_default')
        ,   $this->router->generate('contao_backend')
        );

        return '<p class="tl_error">'.$msg.'</p>';
    }
}
