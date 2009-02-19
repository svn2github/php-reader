<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2006-2009 The PHP Reader Project Workgroup. All rights
 * reserved.
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
 * @subpackage ASF
 * @copyright  Copyright (c) 2006-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>ASF_Extended_Content_Description_Object</i> object implementation.
 * This object contains unlimited number of attribute fields giving more
 * information about the file.
 * 
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_ExtendedContentDescription extends ASF_Object
{
  /** @var Array */
  private $_contentDescriptors = array();

  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader  $reader The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;
    
    $contentDescriptorsCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $contentDescriptorsCount; $i++) {
      $nameLen = $this->_reader->readUInt16LE();
      $name = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16LE($nameLen));
      $valueDataType = $this->_reader->readUInt16LE();
      $valueLen = $this->_reader->readUInt16LE();
      
      switch ($valueDataType) {
      case 0: // string
        $this->_contentDescriptors[$name] = iconv
          ("utf-16le", $this->getOption("encoding"),
           $this->_reader->readString16LE($valueLen));
        break;
      case 1: // byte array
        $this->_contentDescriptors[$name] = $this->_reader->read($valueLen);
        break;
      case 2: // bool
        $this->_contentDescriptors[$name] =
          $this->_reader->readUInt32LE() == 1 ? true : false;
        break;
      case 3: // 32-bit integer
        $this->_contentDescriptors[$name] = $this->_reader->readUInt32LE();
        break;
      case 4: // 64-bit integer
        $this->_contentDescriptors[$name] = $this->_reader->readInt64LE();
        break;
      case 5: // 16-bit integer
        $this->_contentDescriptors[$name] = $this->_reader->readUInt16LE();
        break;
      default:
      }
    }
  }

  /**
   * Returns the value of the specified descriptor or <var>false</var> if there
   * is no such descriptor defined.
   *
   * @param  string $name The name of the descriptor (ie the name of the field).
   * @return string|false
   */
  public function getDescriptor($name)
  {
    if (isset($this->_contentDescriptors[$name]))
      return $this->_contentDescriptors[$name];
    return false;
  }

  /**
   * Sets the given descriptor a new value.
   *
   * @param  string $name  The name of the descriptor.
   * @param  string $value The value of the field.
   * @return string|false
   */
  public function setDescriptor($name, $value)
  {
    $this->_contentDescriptors[$name] = $value;
  }
  
  /**
   * Returns an associate array of all the descriptors defined having the names
   * of the descriptors as the keys.
   *
   * @return Array
   */
  public function getDescriptors() { return $this->_contentDescriptors; }
  
  /**
   * Sets the content descriptor associate array having the descriptor names as
   * array keys and their values as associated value. The descriptor names and
   * all string values must be encoded in the default character encoding given
   * as an option to {@link ASF} class.
   *
   * @param Array $contentDescriptors The content descriptors
   */
  public function setDescriptors($contentDescriptors)
  {
    $this->_contentDescriptors = $contentDescriptors;
  }
  
  /**
   * Returns the whether the object is required to be present, or whether
   * minimum cardinality is 1.
   * 
   * @return boolean
   */
  public function isMandatory() { return false; }
  
  /**
   * Returns whether multiple instances of this object can be present, or
   * whether maximum cardinality is greater than 1.
   * 
   * @return boolean
   */
  public function isMultiple() { return false; }
  
  /**
   * Returns the object data with headers.
   *
   * @return string
   */
  public function __toString()
  {
    $data = Transform::toUInt16LE(count($this->_contentDescriptors));
    
    foreach ($this->_contentDescriptors as $name => $value) {
      $descriptor = iconv
        ($this->getOption("encoding"), "utf-16le", $name ? $name . "\0" : "");
      $data .= Transform::toUInt16LE(strlen($descriptor)) .
        Transform::toString16LE($descriptor);
      
      if (is_string($value)) {
        /* There is no way to distinguish byte arrays from unicode strings and
         * hence the need for a list of fields of type byte array */
        static $byteArray = array (
          "W\0M\0/\0M\0C\0D\0I\0\0\0",
          "W\0M\0/\0U\0s\0e\0r\0W\0e\0b\0U\0R\0L\0\0\0",
          "W\0M\0/\0L\0y\0r\0i\0c\0s\0_\0S\0y\0n\0c\0h\0r\0o\0n\0i\0s\0e\0d\0\0\0",
          "W\0M\0/\0P\0i\0c\0t\0u\0r\0e\0\0\0"
        ); // TODO: Add to the list if you encounter one

        if (in_array($descriptor, $byteArray))
          $data .= Transform::toUInt16LE(1) .
            Transform::toUInt16LE(strlen($value)) . $value;
        else {
          $value = iconv
            ($this->getOption("encoding"), "utf-16le", $value) . "\0\0";
          $data .= Transform::toUInt16LE(0) .
            Transform::toUInt16LE(strlen($value)) .
            Transform::toString16LE($value);
        }
      }
      else if (is_bool($value))
        $data .= Transform::toUInt16LE(2) . Transform::toUInt16LE(4) .
          Transform::toUInt32LE($value ? 1 : 0);
      else if (is_int($value))
        $data .= Transform::toUInt16LE(3) . Transform::toUInt16LE(4) .
          Transform::toUInt32LE($value);
      else if (is_float($value))
        $data .= Transform::toUInt16LE(4) . Transform::toUInt16LE(8) .
          Transform::toInt64LE($value);
      else {
        // Invalid value and there is nothing to be done so cause a fatal error
        require_once("ASF/Exception.php");
        throw new ASF_Exception("Invalid data type");
      }
    }

    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
