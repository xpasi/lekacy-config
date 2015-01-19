<?php

namespace Lekacy\Config\Containers;

/*
* Define a container for settings
*/

class ConfigContainer {

	private $key = null;
	private $value = null;
	private $file = null;
	private $changed = false;

	public function __construct($key,$value = null,$file = null) {
		$this->key = $key;
		$this->value = $value;
		$this->file = $file;
	}

	public function get() {
		return $this->value;
	}

	public function set($value) {
		$this->value = $value;
		$this->changed = true;
		return $this->get();
	}

	/*
	* Returns true if the value has changed during runtime
	*/
	public function changed() {
		return $this->changed;
	}

	/*
	* Get the filename where the file was loaded from
	*/
	public function file() {
		return $this->file;
	}
}