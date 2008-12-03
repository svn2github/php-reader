<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008 The PHP Reader Project Workgroup. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the project workgroup nor the names of its
 *    contributors may be used to endorse or promote products derived from this
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    php-reader
 * @subpackage MPEG
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: Object.php 107 2008-08-03 19:09:16Z svollbehr $
 */

/**#@+ @ignore */
require_once("Reader.php");
require_once("MPEG/Exception.php");
/**#@-*/

/**
 * The base class for all MPEG objects.
 *
 * @package    php-reader
 * @subpackage MPEG
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 107 $
 */
abstract class MPEG_Object
{
  /**
   * The reader object.
   *
   * @var Reader
   */
  protected $_reader;
  
  /**
   * The options array.
   *
   * @var Array
   */
  private $_options;
  
  /**
   * Constructs the class with given parameters.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    $this->_reader = $reader;
    $this->_options = &$options;
  }
  
  /**
   * Returns the options array.
   *
   * @return Array
   */
  public function getOptions() { return $this->_options; }
  
  /**
   * Returns the given option value, or the default value if the option is not
   * defined.
   *
   * @param string $option The name of the option.
   * @param mixed $defaultValue The default value to be returned.
   */
  public function getOption($option, $defaultValue = false)
  {
    if (isset($this->_options[$option]))
      return $this->_options[$option];
    return $defaultValue;
  }
  
  /**
   * Sets the options array. See {@link MPEG} class for available options.
   *
   * @param Array $options The options array.
   */
  public function setOptions(&$options) { $this->_options = &$options; }
  
  /**
   * Sets the given option the given value.
   *
   * @param string $option The name of the option.
   * @param mixed $value The value to set for the option.
   */
  public function setOption($option, $value)
  {
    $this->_options[$option] = $value;
  }
  
  /**
   * Finds and returns the next start code. Start codes are reserved bit
   * patterns in the video file that do not otherwise occur in the video stream.
   * 
   * All start codes are byte aligned and start with the following byte
   * sequence: 0x00 0x00 0x01.
   * 
   * @return integer
   */
  protected function nextStartCode()
  {
    $buffer = "    ";
    for ($i = 0; $i < 4; $i++) {
      $start = $this->_reader->getOffset();
      if (($buffer = substr($buffer, -4) . $this->_reader->read(512)) === false)
        throw new MPEG_Exception("Invalid data");
      $limit = strlen($buffer);
      $pos = 0;
      while ($pos < $limit - 3) {
        if (Transform::fromUInt8($buffer{$pos++}) == 0 &&
            Transform::fromUInt16BE(substr($buffer, $pos, 2)) == 1) {
          if (($pos += 2) < $limit - 2)
            if (Transform::fromUInt16BE(substr($buffer, $pos, 2)) == 0 &&
                Transform::fromUInt8($buffer{$pos + 2}) == 1)
              continue;
          $this->_reader->setOffset($start + $pos - 3);
          return Transform::fromUInt8($buffer{$pos++}) & 0xff | 0x100;
        }
      }
      $this->_reader->setOffset($start + $limit);
    }
    
    /* No start code found within 2048 bytes, the maximum size of a pack */
    throw new MPEG_Exception("Invalid data");
  }
  
  /**
   * Finds and returns the previous start code. Start codes are reserved bit
   * patterns in the video file that do not otherwise occur in the video stream.
   * 
   * All start codes are byte aligned and start with the following byte
   * sequence: 0x00 0x00 0x01.
   * 
   * @return integer
   */
  protected function prevStartCode()
  {
    $buffer = "    ";
    $start;
    $position = $this->_reader->getOffset();
    while ($position > 0) {
      $start = 0;
      $position = $position - 512;
      if ($position < 0)
        throw new MPEG_Exception("Invalid data");
      $this->_reader->setOffset($position);
      $buffer = $this->_reader->read(512) . substr($buffer, 0, 4);
      $pos = 512 - 8;
      while ($pos  > 3) {
        if (Transform::fromUInt8($buffer{$pos}) == 0 &&
            Transform::fromUInt16BE(substr($buffer, $pos + 1, 2)) == 1) {
          
          if ($pos + 2 < 512 &&
              Transform::fromUInt16BE(substr($buffer, $pos + 3, 2)) == 0 &&
              Transform::fromUInt8($buffer{$pos + 5}) == 1) {
            $pos --;
            continue;
          }
          $this->_reader->setOffset($position + $pos);
          return Transform::fromUInt8($buffer{$pos + 3}) & 0xff | 0x100;
        }
        $pos--;
      }
      $this->_reader->setOffset($position = $position + 3);
    }
    return 0;
  }
  
  /**
   * Magic function so that $obj->value will work.
   *
   * @param string $name The field name.
   * @return mixed
   */
  public function __get($name)
  {
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    else throw new MPEG_Exception("Unknown field: " . $name);
  }
  
  /**
   * Magic function so that assignments with $obj->value will work.
   *
   * @param string $name  The field name.
   * @param string $value The field value.
   * @return mixed
   */
  public function __set($name, $value)
  {
    if (method_exists($this, "set" . ucfirst($name)))
      call_user_func
        (array($this, "set" . ucfirst($name)), $value);
    else throw new MPEG_Exception("Unknown field: " . $name);
  }
}
