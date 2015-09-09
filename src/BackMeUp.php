<?php

class BackMeUp extends BackMeUpUtils {

	private $force = false;
	private $password = '';
	private $source = '';

	private function compress7z($srcPath, $dstFile) {
		throw new Exception('7z not implemented yet.');
	}

	private function compressZip($srcPath, $dstFile) {

		$compression = '-9';

		$cmd = $this->zipBinPath . ' '; // . ' -9 -r "' . $dstFile . '" "' . $srcPath . '"';
		$cmd .= $compression . ' ';
		$cmd .= '--recurse-paths ';
		$cmd .= '-p ';

		if (!empty($this->password)) {
			$cmd .= '--password "' . $this->password . '" ';
		}

		$cmd .= '"' . $dstFile . '" '; //destination file
		$cmd .= '"' . $srcPath . '" > /dev/null '; //source folder

		system($cmd, $returnVal);
		if ($returnVal > 0) {
			throw new Exception('There was a problem zipping to file \'' . $dstFile . '\'.');
		}
	}

	private function compressTarGZip($srcPath, $dstFile) {

		if (!empty($this->password)) {
			throw new Exception('Encryption for tar.gz not implemented yet.');
		}

		$cmd = 'GZIP=-9 ' . $this->tarBinPath . ' --create --gzip --file "' . $dstFile . '" -C "' . $srcPath . '" .';
		shell_exec($cmd);
		//confirm file integrity
		$cmd = $this->tarBinPath . ' --list --gzip --file "' . $dstFile . '" > /dev/null';
		system($cmd, $returnVal);
		 if ($returnVal > 0) {
			 throw new Exception('There was a problem checking the integrity of file \'' . $dstFile . '\'.');
		 }
		//  //add compression
		//  $cmd = 'gpg --yes --batch --no-tty ';
		//  if (!empty($this->password)) {
		//    $cmd .= '--passphrase="' . $this->password . '" ';
		//  }
		//  $cmd .= '-c "' . $dstFile . '" > /dev/null ';
		//  system($cmd, $returnVal);
		//  if ($returnVal > 0) {
		//    throw new Exception('There was a problem encrypting the file \'' . $dstFile . '\'.');
		//  }
	 }

	private function compressTar($srcPath, $dstFile) {

		if (!empty($this->password)) {
			throw new Exception('Encryption for tar.gz not implemented yet.');
		}

		$cmd = $this->tarBinPath . ' --create --file "' . $dstFile . '" -C "' . $srcPath . '" .';
		shell_exec($cmd);
		//confirm file integrity
		$cmd = $this->tarBinPath . ' --list --file "' . $dstFile . '" > /dev/null';
		system($cmd, $returnVal);
		 if ($returnVal > 0) {
			 throw new Exception('There was a problem checking the integrity of file \'' . $dstFile . '\'.');
		 }
	}

	private function compress($srcPath, $dstFile, $hash='') {

		$this->timeStart();

		$this->output(" * Backup \"" . $srcPath . "\" to \"" . $dstFile . "\"\n    ");

		$folderHash = '';

		if ($this->force) {
			$this->output("[by force] ");
		}

		//check if hash exist
		if (
			file_exists($dstFile) &&
			file_exists($dstFile . '.crc') &&
			!$this->force
		) {
			$calculatedHash = file_get_contents($dstFile . '.crc');

			$calculatedHashArr = explode(':', $calculatedHash);
			if (count($calculatedHashArr) == 2) {
				$folderHash = $this->calcHash($this->duBinPath . ' -sb "' . $srcPath . '"');
				$fileHash = $this->calcHash($this->catBinPath . ' "' . $dstFile . '"');

				if (
					$calculatedHashArr[0] == $folderHash &&
					$calculatedHashArr[1] == $fileHash
				) {
					//nothing to do here, moving along
					$this->output("no changes, moving along - completed in " . $this->timeEnd() . "\n");
					return;
				}
			}

			$this->output("changes found ");
		}

		$this->output("busy... ");

		switch ($this->type) {
			case '7z':
				$this->compress7z($srcPath, $dstFile);
			break;
			case 'tar':
				$this->compressTar($srcPath, $dstFile);
			break;
			case 'zip':
				$this->compressZip($srcPath, $dstFile);
			break;
			case 'tar.gz':
				$this->compressTarGZip($srcPath, $dstFile);
			break;
		}

		if (empty($folderHash)) {
			$folderHash = $this->calcHash($this->duBinPath . ' -sb "' . $srcPath . '"');
		}
		$fileHash = $this->calcHash($this->catBinPath . ' "' . $dstFile . '"');
		//echo "\nSOURCE FOLD: ".$folderHash."\nSOURCE FILE: ".$fileHash."\n";
		file_put_contents($dstFile . '.crc', $folderHash . ':' . $fileHash);
		//echo "\n".$folderHash.':'.$fileHash."\n";

		$this->output("completed in " . $this->timeEnd() . "\n");
	}

	private function backupRecursively($srcPath, $dstPath, $subfolders=false, $level=0) {
		if ($subfolders) {
			if ($handle = opendir($srcPath)) {
				while (false !== ($file = readdir($handle))) {
					if ('.' === $file || '..' === $file) continue;
					$filePath = $srcPath . DIRECTORY_SEPARATOR . $file;
					if (!is_dir($filePath)) continue;
					$this->backupRecursively($filePath, $dstPath, false, ($level+1));
				}
				closedir($handle);
			}
			return;
		}

		$dstFile = $dstPath . DIRECTORY_SEPARATOR .
			$this->cleanFileName(basename($srcPath)) . '.' . $this->type;
		if ($level > 0) {
			$dstFile = $dstPath . DIRECTORY_SEPARATOR .
				$this->cleanFileName(basename(dirname($srcPath))) . '-' .
				$this->cleanFileName(basename($srcPath)) . '.' . $this->type;
		}

		$this->compress($srcPath, $dstFile);
	}

	private function backup($sourceData=array()) {
		ob_start();
		foreach ($sourceData as $data) {
			if (
				!isset($data->source) ||
				!isset($data->destination)
			) {
				throw new Exception('Bad json file.');
			}
			$subfolders = isset($data->onlysubfolders) ? $data->onlysubfolders : false;
			$this->backupRecursively($data->source, $data->destination, $subfolders);
		}
		ob_end_flush();
	}

	public function run($args=array()) {

		if (PHP_SAPI !== 'cli') {
			throw new Exception('Please execute via CLI.');
		}

		$shortopts  = "";
		$shortopts .= "t:p:s:";
		$shortopts .= "fhv";
		$longopts  = array(
			"type:",
			"password:",
			"source:",
			"force",
			"help",
			"verbose"
		);
		$options = getopt($shortopts, $longopts);

		if (
			count($options) == 0 ||
			isset($options['h']) ||
			isset($options['help'])
		) {
			$this->showHelp();
			return;
		}

		$this->force = isset($options['f']) || isset($options['force']);

		$this->debug = isset($options['v']) || isset($options['verbose']);

		if (isset($options['t']) && !empty($options['t'])) {
			$this->type = $options['t'];
		} else if (isset($options['type']) && !empty($options['type'])) {
			$this->type = $options['type'];
		}

		if (isset($options['p']) && !empty($options['p'])) {
			$this->password = $options['p'];
		} else if (isset($options['password']) && !empty($options['password'])) {
			$this->password = $options['password'];
		}

		if (isset($options['s']) && !empty($options['s'])) {
			$this->source = $options['s'];
		} else if (isset($options['source']) && !empty($options['source'])) {
			$this->source = $options['source'];
		}

		//load json file
		if (
			empty($this->source) ||
			!file_exists($this->source)
		) {
			throw new Exception('Source JSON file missing (try with -s [FILE] or --source=[FILE]).');
		}

		try {
			$jsonData = json_decode(file_get_contents($this->source));
		} catch(Exception $e) {
			throw new Exception('Source JSON file invalid or corrupt.');
		}

		if (empty($jsonData)) {
			throw new Exception('Source JSON file invalid or corrupt.');
		}

		// if ($this->debug) {
		//   echo "Type: " . $this->type . "\n";
		//   echo "File: " . $this->type . "\n";
		//   echo "Force: " . $this->force . "\n";
		//   echo "Password: " . $this->password . "\n";
		//   echo "Source: " . $this->source . "\n";
		// }

		$this->checkEnvironment();

		$this->backup($jsonData);
	}
}
