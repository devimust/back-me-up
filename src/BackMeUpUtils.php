<?php
class BackMeUpUtils {

	protected $tarBinPath = '';
	protected $_7zBinPath = '';
	protected $zipBinPath = '';
	protected $shaBinPath = '';
	protected $duBinPath = '';
	protected $catBinPath = '';
	protected $gpgBinPath = '';

	protected $debug = false;
	protected $type = 'zip';

	private $_timeStart = 0;

	protected function showHelp() {

		$version = defined('BMU_VERSION') ? BMU_VERSION : '(no version found)';

		$output = <<<EOT
Copyright (c) 2015-2016 BackMeUp.
BackMeUp v{$version} (September 4th 2015). Usage:
bmu [OPTION]... [FILE]...
Mandatory arguments to long options are mandatory for short options too.

-t, --type=TYPE         Type of target file to create (can be zip, tar, tar.gz or 7z)
-f, --force             Force overwrite of destination file and checksum
-s, --source=SOURCE     Path to json source file
-p, --password=PASSWORD Password used to encrypt target
-v, --verbose           Display debug output
-h, --help              This screen

Report bugs to <name@domain.com>.


EOT;
		echo $output;
	}

	protected function checkEnvironment() {
		switch ($this->type) {
			case 'tar.gz':
			case 'tar':
				$path = shell_exec('command -v tar');
				$path = str_replace(array("\r", "\n"), '', $path);
				if (empty($path)) {
					throw new Exception('This class cannot be used as it depends on `tar` being installed and available.');
				}
				$this->tarBinPath = $path;
			break;
			case 'zip':
				$path = shell_exec('command -v zip');
				$path = str_replace(array("\r", "\n"), '', $path);
				if (empty($path)) {
					throw new Exception('This class cannot be used as it depends on `zip` being installed and available.');
				}
				$this->zipBinPath = $path;
			break;
			case '7z':
				$path = shell_exec('command -v 7z');
				$path = str_replace(array("\r", "\n"), '', $path);
				if (empty($path)) {
					throw new Exception('This class cannot be used as it depends on `7z` being installed and available.');
				}
				$this->$_7zBinPath = $path;
			break;
		}

		$path = shell_exec('command -v du');
		$path = str_replace(array("\r", "\n"), '', $path);
		if (empty($path)) {
			throw new Exception('This class cannot be used as it depends on `du` being installed and available.');
		}
		$this->duBinPath = $path;

		$path = shell_exec('command -v cat');
		$path = str_replace(array("\r", "\n"), '', $path);
		if (empty($path)) {
			throw new Exception('This class cannot be used as it depends on `cat` being installed and available.');
		}
		$this->catBinPath = $path;

		$path = shell_exec('command -v gpg');
		$path = str_replace(array("\r", "\n"), '', $path);
		if (empty($path)) {
			throw new Exception('This class cannot be used as it depends on `gpg` being installed and available.');
		}
		$this->gpgBinPath = $path;

		$path = shell_exec('command -v sha256sum');
		$path = str_replace(array("\r", "\n"), '', $path);
		if (empty($path)) {
			throw new Exception('This class cannot be used as it depends on `sha256sum` being installed and available.');
		}
		$this->shaBinPath = $path;
	}

	protected function calcHash($cmd) {
		$hash = shell_exec($cmd . ' | ' . $this->shaBinPath);
		if (empty($hash)) {
			return '';
		}
		$hashArr = explode(' ', $hash);
		$hash = isset($hashArr[0]) ? $hashArr[0] : '';
		return $hash;
	}

	protected function output($msg) {
		if ($this->debug) {
			echo $msg;
		}
		ob_flush();
	}

	protected function timeStart() {
		$this->_timeStart = microtime(true);
	}

	protected function timeEnd() {
		$time_end = microtime(true);
		return self::timeDiff($time_end, $this->_timeStart);
	}

	protected static function timeDiff($time1, $time2) {
		$timeCalc = $time1 - $time2;
		if ($timeCalc > (60*60*24)) {$timeCalc = round($timeCalc/60/60/24) . " days";}
		else if ($timeCalc > (60*60)) {$timeCalc = round($timeCalc/60/60) . " hours";}
		else if ($timeCalc > 60) {$timeCalc = round($timeCalc/60) . " minutes";}
		else if ($timeCalc > 0) {$timeCalc = round($timeCalc, 3) . " seconds";}
		return $timeCalc;
	}
}
