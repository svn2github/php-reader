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
 * The <i>Stream Bitrate Properties Object</i> defines the average bit rate of
 * each digital media stream.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_StreamBitrateProperties extends ASF_Object
{
  /** @var Array */
  private $_bitrateRecords = array();
  
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
    
    $bitrateRecordsCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $bitrateRecordsCount; $i++)
      $this->_bitrateRecords[] = array
        ("streamNumber" => ($tmp = $this->_reader->readInt16LE()) & 0x1f,
         "flags" => $tmp >> 5,
         "averageBitrate" => $this->_reader->readUInt32LE());
  }
  
  /**
   * Returns an array of bitrate records. Each record consists of the following
   * keys.
   * 
   *   o streamNumber -- Specifies the number of this stream described by this
   *     record. 0 is an invalid stream. Valid values are between 1 and 127.
   * 
   *   o flags -- These bits are reserved and should be set to 0.
   * 
   *   o averageBitrate -- Specifies the average bit rate of the stream in bits
   *     per second. This value should include an estimate of ASF packet and
   *     payload overhead associated with this stream.
   *
   * @return Array
   */
  public function getBitrateRecords() { return $this->_bitrateRecords; }
  
  /**
   * Sets an array of bitrate records. Each record consists of the following
   * keys.
   * 
   *   o streamNumber -- Specifies the number of this stream described by this
   *     record. 0 is an invalid stream. Valid values are between 1 and 127.
   * 
   *   o flags -- These bits are reserved and should be set to 0.
   * 
   *   o averageBitrate -- Specifies the average bit rate of the stream in bits
   *     per second. This value should include an estimate of ASF packet and
   *     payload overhead associated with this stream.
   * 
   * @param Array $bitrateRecords The array of bitrate records.
   */
  public function setBitrateRecords($bitrateRecords)
  {
    $this->_bitrateRecords = $bitrateRecords;
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
    $data = Transform::toUInt16LE
      ($bitrateRecordsCount = count($this->_bitrateRecords));
    for ($i = 0; $i < $bitrateRecordsCount; $i++)
      $data .= Transform::toUInt16LE
        (($this->_bitrateRecords[$i]["flags"] << 5) |
         ($this->_bitrateRecords[$i]["streamNumber"] & 0x1f)) .
        Transform::toUInt32LE($this->_bitrateRecords[$i]["averageBitrate"]);
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
