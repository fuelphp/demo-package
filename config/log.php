<?php
/**
 * @package    Fuel
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * This is the global configuration for the FuelPHP framework. It contains
 * configuration which is global for all installed applications.
 */

/**
 * Variables passed:
 * $app - This applications instance
 * $log - The Monolog Logger instance for this application
 *
 * If you return an object that implements Psr\Log\LoggerInterface, it will
 * replace the default Logger instance setup by the application
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * step 1: make sure the log directories exist
 */
try
{
	// set the paths and filenames
	$path = realpath(__DIR__.DS.'..'.DS.'logs').DS;

	// get the required folder permissions
	$permission = $app->getConfig()->get('file.chmod.folders', 0777);

	$rootpath = $path.date('Y').DS;
	if ( ! is_dir($rootpath))
	{
		mkdir($rootpath, $permission, true);
		chmod($rootpath, $permission);
	}

	$filepath = $path.date('Y/m').DS;
	if ( ! is_dir($filepath))
	{
		mkdir($filepath, $permission, true);
		chmod($filepath, $permission);
	}

	$filename = $filepath.date('d').'.php';

	$handle = fopen($filename, 'a');
}
catch (\Exception $e)
{
	throw new \RuntimeException('Unable to create or write to the log file. Please check the permissions on '.$path);
}

if ( ! filesize($filename))
{
	fwrite($handle, "<?php defined('APPSPATH') or exit('No direct script access allowed'); ?>".PHP_EOL.PHP_EOL);
	chmod($filename, $app->getConfig()->get('file.chmod.files', 0666));
}
fclose($handle);

/**
 * step 2: create the default streamhandler, and activate the handler
 */

// determine the log level needed
$level = $app->env->name == 'production' ? Logger::ERROR : Logger::DEBUG;

// define the default streamhandler and formatter, and push them on the log instance
$stream = new StreamHandler($filename, $level);
$formatter = new LineFormatter("%level_name% - %datetime% --> %message%".PHP_EOL, "Y-m-d H:i:s");
$stream->setFormatter($formatter);
$log->pushHandler($stream);
