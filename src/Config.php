<?php

namespace Lekacy\Config;

use Lekacy\Config\Exceptions\ConfigFileException;
use Lekacy\Config\Exceptions\ConfigException;
use Lekacy\Config\Containers\ConfigContainer;


define('LEKACY_CONFIG_KEY_SEPARATOR','.');

class Config {

	private static $configuration = [];
	private static $changed = [];

	/*
	* Loads a a configuration file or a whole directory of config files
	*/
	public static function load($location) {
		if (!file_exists($location)) throw new ConfigFileException('File not found: ' . $location);
		// Default to loading a single file
		$files = [$location];
		// If location is a directory, get a list of files from it
		if (is_dir($location)) $files = self::getConfigFilesFromDirectory($location);

		// Process all files
		foreach ($files as $file) {
			self::encodeToInternalFormat(self::loadFromFile($file),[pathinfo($file,PATHINFO_FILENAME)],$file);
		}
	}

	public static function set($key, $value) {
		// Check that key has namespace in it
		if (count(explode(LEKACY_CONFIG_KEY_SEPARATOR,$key)) == 1) throw new ConfigException('Can\'t set configuration key without namespace: ' . $key);
		// Create a new key if neccessary
		if (!isset(self::$configuration[$key])) self::$configuration[$key] = new ConfigContainer($key,$value);
		// Set the value
		self::$configuration[$key]->set($value);
	}

	public static function get($key) {
		if (!isset(self::$configuration[$key])) throw new ConfigException('Configuration key not found: ' . $key);
		return self::$configuration[$key]->get(); 
	}

	/*
	* Reset configuration competely
	*/
	public function reset() {
		self::$configuration = array();
		self::$changed = array();
	}

	/*
	* Saves a configuration file using the same filename that was used to read in the data
	* but allows saving the key to a different directory location
	* It will append any new keys and overwrite old keys.
	*/
	public static function save($key,$location = null) {
		// Check that location exists and is a directory and not a file
		if ($location !== null && (file_exists($location) && !is_dir($location))) throw new ConfigFileException('Save file location is not an directory!');
		// Convert string key into an array
		$akey = explode(LEKACY_CONFIG_KEY_SEPARATOR,$key);
		// Remove the first element as it's the filename context which is automatically set
		$context = array_shift($akey); 
		// Get the current file location as default
		$saveto = pathinfo(self::$configuration[$key]->file(),PATHINFO_DIRNAME);
		// Overwrite current file location if new location has been specified
		if ($location !== null) $saveto = $location;
		// Set the filename (needs to be same as context, or else the key name will be different on load!)
		$saveto .= '/' . $context . '.json';
		// Initialize the data array
		$data = [];
		// Check that the file exists
		if (file_exists($saveto)) {
			// If file exists; read any existing content from the file.
			$data = self::loadFromFile($saveto);
		}

		// Build an recursive array of the key array and set the value at the top level
		$akey = array_reverse($akey);
		$change = [];
		foreach ($akey as $level => $name) {
			// At first level, set the value
			if ($level == 0) $change[$name] = self::$configuration[$key]->get();
			if ($level > 0) $change[$name] = $change;
			if (isset($prev)) unset($change[$prev]);
			$prev = $name;
		}

		// Merge old and new data and encode to JSON (use pretty format as the files need also to be editable by hand!)
		if (!($json = json_encode(array_replace_recursive($data,$change),JSON_PRETTY_PRINT))) throw new ConfigFileException('Error encoding data for saving!');

		// Save the file
		if (file_put_contents($saveto, $json) === false) throw new ConfigFileException('Error saving config file: ' . $saveto);
	}

	/*
	* Returns a list of config files from a directory
	* Note: none recursive
	*/
	private static function getConfigFilesFromDirectory($location) {
		if (get_class(($directory = dir($location))) != 'Directory') throw new ConfigFileException('Could not open directory: ' . $location);
		$files = [];
		while (($entry = $directory->read()) !== false) {
			// Load only files and when extension is JSON
			if (is_file($location . '/' . $entry) && pathinfo($location . '/' . $entry,PATHINFO_EXTENSION) == 'json') {
				$files[] = $location . '/' . $entry;
			}
		}
		$directory->close();
		return $files;
	}

	/*
	* Reads a json config file
	*/
	private static function loadFromFile($location) {
		if (!is_readable($location)) throw new ConfigFileException('Can\'t read file: ' . $location);
		$data = json_decode(file_get_contents($location),true);
		if (json_last_error() !== JSON_ERROR_NONE) throw new ConfigFileException('Error while processing configuration file: ' . $location);
		return $data;
	}

	/*
	* Encodes configuration array into flat dot separated values for usability
	* This is an iteration function which will call itself when neccessary
	* $location = File path from where the files was loaded from
	*/
	private static function encodeToInternalFormat(array $data, array $depth, $location = null) {
		foreach ($data as $k => $v) {
			array_push($depth, $k);
			if (is_array($v)) {
				self::encodeToInternalFormat($v,$depth,$location);
			} else {
				$key = implode(LEKACY_CONFIG_KEY_SEPARATOR,$depth);
				self::$configuration[$key] = new ConfigContainer($key,$v,$location);
			}
			array_pop($depth);
		}
	}
}


