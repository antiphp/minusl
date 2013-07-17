<?php
/**
 * class file
 *
 * svn information:
 * @author $Author: Christian $
 * @date $Date: 2013-07-17 00:22:56 +0200 (Mi, 17 Jul 2013) $
 * @since $Rev: 2 $
 */
namespace AntiPhp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use AntiPhp\Validate\ValidateInterface;

class RunCommand extends Command
{
    /**
     * @var callback
     */
    const HASH_DEFAULT = 'md5_file';

    /**
     * @var integer
     */
    const AUTO_SAVE_INTERVAL_DEFAULT = 80;

    /**
     * JSON key of the file's hash value (or any similar value like last modified time)
     * @var string
     */
    const INDEX_HASH = 'hash';

    /**
     * JSON key of the file's 'saved time' value in seconds
     * @var string
     */
    const INDEX_SAVED_TIME = 'time';

    /**
     * JSON key of the file's checked state (true = checked and valid)
     * @var string
     */
    const INDEX_CHECKED = 'valid';

    /**
     * JSON key of the file's error message (if there is any)
     * @var string
     */
    const INDEX_ERROR = 'error';

    /**
     * list of validators matching files depending on their extension
     * @var ValidateInterface[]
     */
    protected $extensionValidators = array();

    /**
     * @param ValidateInterface $validator
     * @param string $extension
     */
    public function addExtensionValidator(ValidateInterface $validator, $extension)
    {
        $extensions = (array)$extension;
        foreach ($extensions as $extension) {
            $extension = mb_strtolower($extension);
            $this->extensionValidators[$extension] = $validator;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('run');
        $this->setDescription('Check syntax of PHP files');
        $this->addArgument(
            'directory',
            InputArgument::IS_ARRAY,
            '',
            array(getcwd())
        );
        $this->addOption(
            'hash',
            null,
            InputOption::VALUE_OPTIONAL,
            '',
            static::HASH_DEFAULT
        );
        $this->addOption(
            'cache-file',
            null,
            InputOption::VALUE_OPTIONAL,
            '',
            getcwd() . '/minusl.json'
        );
        $this->addOption(
            'auto-save-interval',
            null,
            InputOption::VALUE_OPTIONAL,
            '',
            static::AUTO_SAVE_INTERVAL_DEFAULT
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getApplication()->getName());
        $directories = $input->getArgument('directory');
        $hashFunction = $this->createHashFunction($input->getOption('hash'));
        $cacheFilename = $input->getOption('cache-file');
        $files = $this->loadCache($cacheFilename);
        $autoSave = 0;
        $autoSaveInterval = max(0, $input->getOption('auto-save-interval'));
        $verbose = $input->getOption('verbose');
        $totalSavedTime = 0.0;
        $errors = array();
        $countFiles = 0;
        $countIgnored = 0;
        $countExtensions = array();
        $countChecked = 0;
        $countValidatedCache = 0;
        $countValidatedSuccess = 0;
        $countValidatedError = 0;
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                throw new \InvalidArgumentException('invalid directory [directory=' . $directory . ']');
            }
            $directory = realpath($directory);
            $output->writeln(PHP_EOL . $directory);
            if (!is_readable($directory)) {
                throw new \InvalidArgumentException('directory not readable [directory=' . $directory . ']');
            }
            $directoryIterator = new \RecursiveDirectoryIterator($directory);
            foreach (new \RecursiveIteratorIterator($directoryIterator) as $fileInfo) {
                /**
                 * @var \SplFileInfo $fileInfo
                 */
                if (!$fileInfo->isFile()) {
                    continue;
                }
                ++$countFiles;
                $extension = $fileInfo->getExtension();
                if (!$this->hasValidatorForExtension($extension)) {
                    ++$countIgnored;
                    continue;
                }
                if (!array_key_exists($extension, $countExtensions)) {
                    $countExtensions[$extension] = 0;
                }
                ++$countExtensions[$extension];
                $filename = $fileInfo->getRealPath();
                $hash = call_user_func($hashFunction, $filename);
                if (!array_key_exists($filename, $files)) {
                    $file = array(
                        static::INDEX_HASH => $hash,
                        static::INDEX_SAVED_TIME => 0.0,
                        static::INDEX_CHECKED => false
                    );
                } else {
                    $file = $files[$filename];
                    if ($file[static::INDEX_HASH] !== $hash) {
                        $file[static::INDEX_HASH] = $hash;
                        $file[static::INDEX_CHECKED] = false;
                    }
                }
                ++$countChecked;
                if ($file[static::INDEX_CHECKED]) {
                    ++$countValidatedCache;
                    $totalSavedTime += $file[static::INDEX_SAVED_TIME];
                    $this->outputWrite(
                        $output,
                        '[.] ' . $filename,
                        '.'
                    );
                    continue;
                }
                $validator = $this->getValidatorByExtension($extension);
                $microtimeStart = microtime(false);
                $isValid = $validator->isValid($filename);
                $microtimeEnd = microtime(false);
                $file[static::INDEX_CHECKED] = $isValid;
                if ($isValid) {
                    ++$countValidatedSuccess;
                    $microtimeStart = explode(' ', $microtimeStart, 2);
                    $microtimeEnd = explode(' ', $microtimeEnd, 2);
                    $microtimeDiff = ($microtimeEnd[1] - $microtimeStart[1]) + ($microtimeEnd[0] - $microtimeStart[0]);
                    $file[static::INDEX_SAVED_TIME] = $microtimeDiff;
                    unset($file[static::INDEX_ERROR]);
                    $this->outputWrite(
                        $output,
                        '[.] ' . $filename,
                        '.'
                    );
                } else {
                    ++$countValidatedError;
                    $error = $validator->getError();
                    $errors[$filename] = $error;
                    $file[static::INDEX_ERROR] = $error;
                    $this->outputWrite(
                        $output,
                        '<error>[E] ' . $filename . '</error>',
                        '<error>E</error>'
                    );
                }
                $files[$filename] = $file;
                if (++$autoSave % $autoSaveInterval === 0) {
                    $this->saveCache($cacheFilename, $files);
                }
            }
            $output->write(PHP_EOL);
        }
        $this->saveCache($cacheFilename, $files);
        $this->generateErrorSummary(
            $output,
            $errors
        );
        $countExtensions = array_combine(
            array_map(function ($extension) { return 'files *.' . $extension; }, array_keys($countExtensions)),
            array_values($countExtensions)
        );
        $summaryKeyValues = array_merge(
            array(
                'files' => $countFiles,
                'files ignored' => $countIgnored,
                'files checked' => $countChecked,
            ),
            $countExtensions,
            array(
                'files valid' => $countValidatedSuccess,
                'files valid from cache' => $countValidatedCache,
                'files invalid' => $countValidatedError,
                'saved time' => \number_format($totalSavedTime, 2) . 's'
            )
        );
        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $this->generateSummary(
                $output,
                'Option summary:',
                array(
                    'hash' => $input->getOption('hash'),
                    'auto save interval' => $autoSaveInterval,
                    'cache file' => $cacheFilename
                )
            );
        }
        $this->generateSummary(
            $output,
            'General summary:',
            $summaryKeyValues
        );
        $success = count($errors) === 0;
        $this->generateExitMessage($output, $success);
        $exitCode = $success ? 0 : 1;
        return $exitCode;
    }

    /**
     * retrieve the cache file
     *
     * @param string $filename
     * @return array
     */
    protected function loadCache($filename)
    {
        $content = array();
        if (!is_file($filename)) {
            touch($filename);
        } else {
            $content = json_decode(file_get_contents($filename), JSON_OBJECT_AS_ARRAY);
            if (!is_array($content)) {
                $content = array();
            }
        }
        return $content;
    }

    /**
     * save to cache
     *
     * @param string $filename
     * @param array $files
     */
    protected function saveCache($filename, array $files)
    {
        $content = json_encode($files, JSON_PRETTY_PRINT);
        file_put_contents($filename, $content);
    }

    /**
     * @param string $extension
     * @return boolean
     */
    protected function hasValidatorForExtension($extension)
    {
        $validator = $this->getValidatorByExtension($extension);
        $hasValidator = $validator !== null;
        return $hasValidator;
    }

    /**
     * @param string $extension
     * @return \AntiPhp\Console\Command\ValidateInterface
     */
    protected function getValidatorByExtension($extension)
    {
        $extension = mb_strtolower($extension);
        if (!array_key_exists($extension, $this->extensionValidators)) {
            return null;
        }
        $validator = $this->extensionValidators[$extension];
        return $validator;
    }

    /**
     * @param OutputInterface $output
     * @param array $errors
     */
    protected function generateErrorSummary(OutputInterface $output, array $errors)
    {
        $count = count($errors);
        if ($count === 0) {
            return;
        }
        $indexLength = max(2, mb_strlen($count));
        $output->writeln('');
        $output->writeln('Error summary (' . $count . '):');
        $index = 1;
        foreach ($errors as $error) {
            $output->writeln('    [' . \str_pad($index, $indexLength, '0', STR_PAD_LEFT) . '] ' . $error);
            ++$index;
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $title
     * @param array $keyValues
     */
    protected function generateSummary(OutputInterface $output, $title, array $keyValues)
    {
        $output->writeln('');
        $output->writeln($title);
        $maxKeyLength = 25; // max(array_map('mb_strlen', array_keys($keyValues))) + 2;
        foreach ($keyValues as $key => $value) {
            $output->writeln(
                '    ' .
                \str_pad($key, $maxKeyLength, '.', STR_PAD_RIGHT) .
                ' ' .
                $value
            );
        }
    }

    /**
     * @param OutputInterface $output
     * @param boolean $success
     */
    protected function generateExitMessage(OutputInterface $output, $success)
    {
        $output->writeln('');
        if ($success) {
            $output->writeln('Success.');
        } else {
            $output->writeln('<error>Fail.</error>');
        }
    }

    /**
     * create the callback function that checks whether a file has changed and we need to check it again or not
     *
     * @param string $hashFunction
     * @throws \InvalidArgumentException
     * @return callback
     */
    protected function createHashFunction($hashFunction)
    {
        $hashFunction = mb_strtolower($hashFunction);
        if (!$hashFunction || $hashFunction === 'md5' || $hashFunction === 'md5_file') {
            $hashFunction = 'md5_file';
        } elseif ($hashFunction ==='sha1' || $hashFunction === 'sha1_file') {
            $hashFunction = 'sha1_file';
        } elseif ($hashFunction === 'filemtime') {
            $hashFunction = 'filemtime';
        } else {
            if (!is_callable($hashFunction)) {
                $hashAlgorithms = hash_algos();
                if (!in_array($hashFunction, $hashAlgorithms)) {
                    throw new \InvalidArgumentException('invalid hash method provided, must be a valid function or hash algorithm [' . implode(', ', $hashAlgorithms) . ']');
                }
                $hashAlgorithm = $hashFunction;
                $hashFunction = function ($string) use ($hashAlgorithm) {
                    return hash($hashAlgorithm, $string);
                };
            }
            $hashFunction = function ($filename) use ($hashFunction) {
                return call_user_func($hashFunction, file_get_contents($filename));
            };
        }
        return $hashFunction;
    }

    /**
     * @param OutputInterface $output
     * @param string $longMessage
     * @param string $shortMessage
     * @param boolean $longMessageWithNewLine
     * @param boolean $shortMessageWithNewLine
     */
    protected function outputWrite(OutputInterface $output, $longMessage, $shortMessage, $longMessageWithNewLine = true, $shortMessageWithNewLine = false)
    {
        $message = $longMessage;
        $newLine = $longMessageWithNewLine;
        $showLongMessage = OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity();
        if (!$showLongMessage) {
            $message = $shortMessage;
            $newLine = $shortMessageWithNewLine;
        }
        $output->write($message, $newLine);
    }
}