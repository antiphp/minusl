#!/usr/bin/env php
<?php
/**
 * cli script file
 *
 * vcs information:
 * @author $Author$
 * @date $Date$
 * @version $Rev$
 */
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Application;
use AntiPhp\Console\Command\RunCommand;
use AntiPhp\Validate\Php;
use AntiPhp\Validate\Json;
use AntiPhp\Validate\Xml;
use AntiPhp\Validate\Ini;

/**
 * @var string application's name
 */
define('MINUSL_APPLICATION_NAME', 'PHP MinusL (-l) Cached syntax checker by Christian Reinecke');

/**
 * @var string application's version
 */
define('MINUSL_APPLICATION_VERSION', '1.0.0');

try {
    $autoloadFilename =  __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadFilename)) {
        throw new RuntimeException($autoloadFilename . ' not found for autoloading, please run \'php composer.phar dump-autoload\' first');
    }
    require $autoloadFilename;

    // setup the run command
    $runCommand = new RunCommand();
    $runCommand->addExtensionValidator(new Php\Syntax(), array('php', 'phtml'));
    $runCommand->addExtensionValidator(new Json\Syntax(), 'json');
    $runCommand->addExtensionValidator(new Xml\Syntax\SimpleXml(), 'xml');
    $runCommand->addExtensionValidator(new Ini\Syntax(), 'ini');

    // setup the application and assign the run command
    $console = new Application(MINUSL_APPLICATION_NAME, MINUSL_APPLICATION_VERSION);
    $console->addCommands(array($runCommand));
    $console->run();
} catch (Exception $e) {
    // handle uncaught exceptions
    echo MINUSL_APPLICATION_NAME, PHP_EOL, PHP_EOL, '    [', get_class($e), '] ', $e->getMessage();
    exit(1);
}