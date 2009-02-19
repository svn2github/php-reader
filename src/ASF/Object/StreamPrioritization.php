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
 * The <i>Stream Prioritization Object</i> indicates the author's intentions as
 * to which streams should or should not be dropped in response to varying
 * network congestion situations. There may be special cases where this
 * preferential order may be ignored (for example, the user hits the "mute"
 * button). Generally it is expected that implementations will try to honor the
 * author's preference.
 * 
 * The priority of each stream is indicated by how early in the list that
 * stream's stream number is listed (in other words, the list is ordered in
 * terms of decreasing priority).
 * 
 * The Mandatory flag field shall be set if the author wants that stream kept
 * "regardless". If this flag is not set, then that indicates that the stream
 * should be dropped in response to network congestion situations. Non-mandatory
 * streams must never be assigned a higher priority than mandatory streams.
 * 
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_StreamPrioritization extends ASF_Object
{
  /** @var Array */
  private $_priorityRecords = array();
  
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
    
    $priorityRecordCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $priorityRecordCount; $i++)
      $this->_priorityRecords[] = array
        ("streamNumber" => $this->_reader->readUInt16LE(),
         "flags"        => $this->_reader->readUInt16LE());
  }
  
  /**
   * Returns an array of records. Each record consists of the following keys.
   * 
   *   o streamNumber -- Specifies the stream number. Valid values are between
   *     1 and 127.
   *
   *   o flags -- Specifies the flags. The mandatory flag is the bit 1 (LSB).
   * 
   * @return Array
   */
  public function getPriorityRecords() { return $this->_priorityRecords; }
  
  /**
   * Sets the array of records. Each record consists of the following keys.
   * 
   *   o streamNumber -- Specifies the stream number. Valid values are between
   *     1 and 127.
   *
   *   o flags -- Specifies the flags. The mandatory flag is the bit 1 (LSB).
   * 
   * @param Array $priorityRecords The array of records.
   */
  public function setPriorityRecords($priorityRecords)
  {
    $this->_priorityRecords = $priorityRecords;
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
      ($priorityRecordCount = count($this->_priorityRecords));
    for ($i = 0; $i < $priorityRecordCount; $i++)
      $data .=
        Transform::toUInt16LE($this->_priorityRecords[$i]["streamNumber"]) .
        Transform::toUInt16LE($this->_priorityRecords[$i]["flags"]);
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
