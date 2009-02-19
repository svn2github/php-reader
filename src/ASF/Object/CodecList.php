<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008-2009 The PHP Reader Project Workgroup. All rights
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
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Codec List Object</i> provides user-friendly information about the
 * codecs and formats used to encode the content found in the ASF file.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_CodecList extends ASF_Object
{
  const VIDEO_CODEC = 0x1;
  const AUDIO_CODEC = 0x2;
  const UNKNOWN_CODEC = 0xffff;
  
  /** @var string */
  private $_reserved;
  
  /** @var Array */
  private $_entries = array();
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;
    
    $this->_reserved = $this->_reader->readGUID();
    $codecEntriesCount = $this->_reader->readUInt32LE();
    for ($i = 0; $i < $codecEntriesCount; $i++) {
      $entry = array("type" => $this->_reader->readUInt16LE());
      $codecNameLength = $this->_reader->readUInt16LE() * 2;
      $entry["codecName"] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16LE($codecNameLength));
      $codecDescriptionLength = $this->_reader->readUInt16LE() * 2;
      $entry["codecDescription"] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16LE($codecDescriptionLength));
      $codecInformationLength = $this->_reader->readUInt16LE();
      $entry["codecInformation"] =
        $this->_reader->read($codecInformationLength);
      $this->_entries[] = $entry;
    }
  }

  /**
   * Returns the array of codec entries. Each record consists of the following
   * keys.
   * 
   *   o type -- Specifies the type of the codec used. Use one of the following
   *     values: VIDEO_CODEC, AUDIO_CODEC, or UNKNOWN_CODEC.
   * 
   *   o codecName -- Specifies the name of the codec used to create the
   *     content.
   *
   *   o codecDescription -- Specifies the description of the format used to
   *     create the content.
   *
   *   o codecInformation -- Specifies an opaque array of information bytes
   *     about the codec used to create the content. The meaning of these bytes
   *     is determined by the codec.
   * 
   * @return Array
   */
  public function getEntries() { return $this->_entries; }

  /**
   * Sets the array of codec entries. Each record must consist of the following
   * keys.
   * 
   *   o codecName -- Specifies the name of the codec used to create the
   *     content.
   *
   *   o codecDescription -- Specifies the description of the format used to
   *     create the content.
   *
   *   o codecInformation -- Specifies an opaque array of information bytes
   *     about the codec used to create the content. The meaning of these bytes
   *     is determined by the codec.
   * 
   * @param Array $entries The array of codec entries.
   */
  public function setEntries($entries) { $this->_entries = $entries; }
  
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
    $data = Transform::toGUID($this->_reserved) .
      Transform::toUInt32LE($codecEntriesCount = count($this->_entries));
    for ($i = 0; $i < $codecEntriesCount; $i++) {
      $data .= Transform::toUInt16LE($this->_entries[$i]["type"]) .
        Transform::toUInt16LE(strlen($codecName = iconv
          ($this->getOption("encoding"), "utf-16le",
           $this->_entries[$i]["codecName"]) . "\0\0") / 2) .
        Transform::toString16LE($codecName) .
        Transform::toUInt16LE(strlen($codecDescription = iconv
          ($this->getOption("encoding"), "utf-16le",
           $this->_entries[$i]["codecDescription"]) . "\0\0") / 2) .
        Transform::toString16LE($codecDescription) .
        Transform::toUInt16LE(strlen($this->_entries[$i]["codecInformation"])) .
        $this->_entries[$i]["codecInformation"];
    }
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
