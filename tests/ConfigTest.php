<?php

namespace Lekacy\Config\Tests;

use Lekacy\Config\Config;


class ConfigTest extends \PHPUnit_Framework_TestCase {

  // Test setting and getting a value when nothing has been loaded
  public function testEmptyGetSet()
  {
  	Config::set('emptygetset.value',true);
    $this->assertTrue(Config::get('emptygetset.value'));
  }

  // Test empty namespace
  /**
   * @expectedException Lekacy\Config\Exceptions\ConfigException
   */
  public function testEmptyNamespaceSet()
  {
    Config::set('test',true);
  }

  // Test loading a file with empty location
  /**
   * @expectedException Lekacy\Config\Exceptions\ConfigFileException
   */
  public function testLoadException()
  {
  	Config::load('');
  }

  // Test load single file
  public function testLoadFile()
  {
  	Config::load(__DIR__ . '/configExamples/1/first.json');
  	$this->assertEquals('test1 value',Config::get('first.test1'));
  }

  // Test over write existing value
  public function testOverwriteGetSet()
  {
  	Config::set('first.test1',true);
    $this->assertTrue(Config::get('first.test1'));
  }

  // Test load a directory
  public function testLoadDirectory()
  {
  	Config::load(__DIR__ . '/configExamples/dirtest/');
  	$this->assertTrue(Config::get('dirtest1.dirTest'));
  	$this->assertTrue(Config::get('dirtest2.dirTest'));
  }

  // Test load overwrite
  public function testLoadOverWrite()
  {
   	$this->assertEquals('test2 value',Config::get('first.test2'));
  	Config::load(__DIR__ . '/configExamples/2/');
  	$this->assertEquals('test3 value',Config::get('first.test3'));
  	$this->assertEquals('test2 value second pass',Config::get('first.test2'));
  }


  // Test Reset
  /**
   * @expectedException Lekacy\Config\Exceptions\ConfigException
   */
  public function testReset()
  {
  	Config::set('reset.test',true);
  	Config::reset();
		$this->assertTrue(Config::get('reset.test'));
  }


  // Test Saving
  public function testSave()
  {
  	$loc = __DIR__ . '/configExamples/tmp/';
  	if (file_exists($loc . 'savetest.json')) unlink($loc . 'savetest.json');
  	$savetestvalue = md5(rand(0,10000) . rand(0,10000));
  	Config::reset();
		Config::set('savetest.testing',$savetestvalue);
  	Config::save('savetest.testing',$loc);
    $this->assertTrue(file_exists($loc . 'savetest.json'));
  	Config::reset();
		Config::load($loc);
  	$this->assertEquals($savetestvalue,Config::get('savetest.testing'));
    if (file_exists($loc . 'savetest.json')) unlink($loc . 'savetest.json');
  }

}