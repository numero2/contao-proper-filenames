<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenamesBundle\Command;

use Contao\Config;
use Contao\CoreBundle\Filesystem\Dbafs\ChangeSet\ChangeSet;
use Contao\CoreBundle\Filesystem\Dbafs\DbafsManager;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Dbafs;
use Contao\Files;
use Contao\FilesModel;
use Contao\Folder;
use Doctrine\DBAL\Connection;
use numero2\ProperFilenamesBundle\Util\FilenamesUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;


#[AsCommand(
    name: 'contao:proper-filenames:sanitize',
    description: 'Sanitizes the file and folder names of the given path inside contao files folder.',
)]
class CleanFilesCommand extends Command implements FrameworkAwareInterface {

    use FrameworkAwareTrait;


    /**
     * @var string
     */
    private string $projectDir;

    /**
     * @var Symfony\Component\Filesystem\Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Doctrine\DBAL\Connection
     */
    private Connection $connection;

    /**
     * @var array
     */
    private array $settings;


    public function __construct( string $projectDir, string $uploadPath, Filesystem $filesystem, Connection $connection ) {

        parent::__construct();

        $this->projectDir = $projectDir;
        $this->uploadPath = $uploadPath;
        $this->filesystem = $filesystem;
        $this->connection = $connection;

        $this->settings = [];
    }


    protected function configure(): void {

        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Path for what will be cleaned.')

            ->addOption('folders-only', null, InputOption::VALUE_NONE, 'Only clean folders.')
            ->addOption('files-only', null, InputOption::VALUE_NONE, 'Only clean files.')
            ->addOption('recursive', 'r', InputOption::VALUE_NONE, 'Clean path recursively')
            ->addOption('max-depth', 'd', InputOption::VALUE_OPTIONAL, 'Only scan folder up to this depth.', -1)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show current and new name of the file or folder without changeing anything.')

            ->setHelp(
                'Be careful this action cannot easily be reversed!'. "\n" .
                'Files and Folders not synced to contao will not be cleaned!'. "\n" .
                'This command will respect the settings like "excludeFileExtensions", "doNotTrimFilenames", "doNotSanitize", ... for all files and folders it discovers and clean them accordingly.'
            )
        ;
    }


    protected function execute( InputInterface $input, OutputInterface $output ): int {

        set_time_limit(0);

        $this->framework->initialize();
        $io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('path');

        $this->loadSettings();

        if( empty($this->settings['checkFilenames']) || empty($this->settings['filenameValidCharacters']) ) {

            $io->success("Proper filenames is not enabled in settings.");
            return Command::SUCCESS;
        }

        $optFoldersOnly = $input->getOption('folders-only');
        $optFilesOnly = $input->getOption('files-only');
        $optRecursive = $input->getOption('recursive');
        $optMaxDepth = intval($input->getOption('max-depth'));
        $optDryRun = $input->getOption('dry-run');

        $optNoInteraction = $input->getOption('no-interaction');


        $filesPath = Path::join($this->uploadPath, $path);
        $basePath = Path::join($this->projectDir, $filesPath);

        if( !$this->filesystem->exists($basePath) ) {

            $io->error('Path not found: "'. $filesPath .'"');
            return Command::FAILURE;
        }

        $pathsToRename = $this->findPaths($filesPath, [
            'folders-only' => $optFoldersOnly
        ,   'files-only' => $optFilesOnly
        ,   'recursive' => $optRecursive
        ,   'max-depth' => $optMaxDepth
        ,   'level' => 0
        ]);

        $countFiles = 0;
        $countDirs = 0;

        foreach( $pathsToRename as $key => $data ) {

            if( $data['type'] === 'file' ) {
                $countFiles += 1;
            } else if( $data['type'] === 'folder' ) {
                $countDirs += 1;
            }
        }

        if( !$optNoInteraction ) {

            $io->section('Cleaning path "' . $filesPath . '"');

            $io->writeln(' * Found folders: ' . $countDirs);
            $io->writeln(' * Found files: ' . $countFiles);
        }

        if( $countFiles === 0 && $countDirs === 0 ) {

            $io->success("Nothing found for cleaning.");
            return Command::SUCCESS;
        }


        if( $optDryRun ) {

            $io->section('Result, only display changes');

        } else {

            if( !$optNoInteraction && !$io->confirm('Execute cleaning files?') ) {
                return Command::SUCCESS;
            }

            $io->section('Renaming...');
        }

        $files = Files::getInstance();
        $foldersToDo = [];


        $countFiles = 0;
        $countDirs = 0;

        $table = new Table($output);
        $table->setHeaders(['Type', 'Old path', 'New name']);

        foreach( $pathsToRename as $path => $entry ) {

            $fullPath = Path::join($this->projectDir, $path);
            $info = pathinfo($fullPath);

            $oldFileName = basename($path);
            $newFileName = null;

            if( $entry['type'] === 'file' ) {

                $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename']) . '.' . strtolower($info['extension']);

            } else if( $entry['type'] === 'folder' ) {

                $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename']);
            }

            if( $newFileName && $oldFileName !== $newFileName ) {

                if( $optDryRun ) {

                    if( $entry['type'] === 'file' ) {
                        $countFiles += 1;
                    } else if( $entry['type'] === 'folder' ) {
                        $countDirs += 1;
                    }

                    $table->addRow([$entry['type'], $path, $newFileName]);

                } else {

                    if( $entry['type'] === 'file' ) {

                        $newPath = Path::join(dirname($path), $newFileName);

                        $countFiles += 1;
                        $table->addRow([$entry['type'], $path, $newFileName]);

                        if( $this->filesystem->exists(Path::join($this->projectDir, $newPath)) || !$files->rename($path, $newPath) ) {

                            $table->render();

                            $io->error('Could not rename file: "'. $path .'" to "'. $newFileName .'"');
                            return Command::FAILURE;
                        }

                        Dbafs::moveResource($path, $newPath);

                    } else if( $entry['type'] === 'folder' ) {

                        $foldersToDo[$path] = $newFileName;

                    }
                }
            }
        }

        if( !$optDryRun && !empty($foldersToDo) ) {

            foreach( array_reverse($foldersToDo) as $path => $newName ) {

                $newPath = Path::join(dirname($path), $newName);

                $countDirs += 1;
                $table->addRow([$entry['type'], $path, $newName]);

                if( $this->filesystem->exists(Path::join($this->projectDir, $newPath)) || !$files->rename($path, $newPath) ) {

                    $table->render();

                    $io->error('Could not rename directory: "'. $path .'" to "'. $newName .'"');
                    return Command::FAILURE;
                }

                Dbafs::moveResource($path, $newPath);
            }
        }

        if( $countFiles || $countDirs ) {

            $table->render();

            $io->writeln(' Total renames files: '. $countFiles .' | folders: '. $countDirs);
            $io->success("Cleaning done.");

        } else {

            $io->success("Nothing found for cleaning.");
        }

        return Command::SUCCESS;
    }


    private function loadSettings(): void {

        $configKeys = ['checkFilenames','filenameValidCharacters', 'excludeFileExtensions'];
        $settings = [];

        foreach( $configKeys as $key ) {
            $settings[$key] = Config::get($key);
        }

        $settings['excludeFileExtensions'] = explode(',', $settings['excludeFileExtensions']);

        $this->settings = $settings;
    }


    private function findPaths( string $path, array $flags ): array {

        $fullPath = Path::join($this->projectDir, $path);

        $paths = [];
        $entry = $this->generateEntry($path);

        // if empty this means file/folder does not exist
        if( empty($entry) ) {
            return [];
        }

        // check if skip based on parent folders and file settings
        if( $this->ignorePath($entry, true) ) {
            return [];
        }

        // also use folder content
        if( $flags['recursive'] ) {
            if( $entry['type'] === 'folder' ) {

                $subPaths = $this->findSubPaths($entry, $flags);

                if( count($subPaths) ) {
                    $paths = array_merge($paths, $subPaths);
                }
            }
        }

        return $paths;
    }


    private function findSubPaths( array $entry, array $flags ): array {

        $path = $entry['model']->path ?? null;
        $type = $entry['type'] ?? 'unknown';

        if( $path === null ) {
            return [];
        }

        if( $flags['max-depth'] >= 0 && $flags['level'] > $flags['max-depth'] ) {
            return [];
        }

        if( $this->ignorePath($entry, true) ) {
            return [];
        }

        $paths = [];
        if( (!$flags['files-only'] && !$flags['folders-only'])
            || ($flags['files-only'] && $type === 'file')
            || ($flags['folders-only'] && $type === 'folder')
            ) {

            $paths[$path] = $entry;
        }

        // also use folder content
        if( $flags['recursive'] ) {
            if( $entry['type'] === 'folder' ) {

                $children = Folder::scan( Path::join($this->projectDir, $path));

                $flags['level'] += 1;

                foreach( $children as $file ) {

                    $subPath = Path::join($path, $file);
                    $subEntry = $this->generateEntry($subPath);

                    $subPaths = $this->findSubPaths($subEntry, $flags);

                    if( count($subPaths) ) {
                        $paths = array_merge($paths, $subPaths);
                    }
                }
            }
        }

        return $paths;
    }


    private function generateEntry( string $path ): array {

        $fullPath = Path::join($this->projectDir, $path);

        if( !$this->filesystem->exists($fullPath) ) {
            return [];
        }

        $data = [];

        if( is_file($fullPath) ) {

            $data['type'] = 'file';

        } else if( is_dir($fullPath) ) {

            $data['type'] = 'folder';

        } else {

            return ['type' => 'unknown'];
        }

        if( $path === $this->uploadPath ) {

            $data['model'] = (object)[
                'path' => $path
            ];
        } else {

            $data['model'] = FilesModel::findByPath($path);
        }


        return $data;
    }


    private function ignorePath( array $entry, bool $checkUpwards=false ): bool {

        $path = $entry['model']->path ?? null;

        if( $path === null ) {
            return true;
        }

        if( $entry['type'] === 'folder' ) {

            if( !array_key_exists($path, $this->settings['skipFolers'] ?? []) ) {
                $this->settings['skipFolers'][$path] = $this->ignoreFolder($entry);
            }
            if( $this->settings['skipFolers'][$path] ?? false ) {
                return true;
            }

        } else if( $entry['type'] === 'file' ) {

            if( $this->ignoreFile($entry) ) {
                return true;
            }
        }

        if( $checkUpwards ) {

            $parent = dirname($path);

            // end recursion on upload path as no model for the root
            if( $path === $this->uploadPath || $parent === $this->uploadPath ) {
                return false;
            }

            $parentEntry = $this->generateEntry($parent);

            return $this->ignorePath($parentEntry, $checkUpwards);
        }

        return false;
    }


    private function ignoreFolder( array $entry ): bool {

        $path = $entry['model']->path ?? null;

        if( $path === null ) {
            return true;
        }

        if( !empty($entry['model']->doNotSanitize) ) {
            return true;
        }

        $folder = new Folder($path);

        if( $folder->isUnsynchronized() ) {
            return true;
        }

        return false;
    }


    private function ignoreFile( array $entry ): bool {

        $extension = $entry['model']->extension ?? null;

        if( $extension && in_array($extension, $this->settings['excludeFileExtensions']) ) {
            return true;
        }

        return false;
    }
}
