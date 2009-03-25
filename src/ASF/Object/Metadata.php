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
 * The <i>Metadata Object</i> permits authors to store stream-based metadata in
 * a file. This object supports the same types of metadata information as the
 * <i>Extended Content Description Object</i> except that it also allows a
 * stream number to be specified.
 *
 * @todo       Implement better handling of various types of attributes
 *             according to http://msdn.microsoft.com/en-us/library/aa384495(VS.85).aspx
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_Metadata extends ASF_Object
{
  /** @var Array */
  private $_descriptionRecords = array();
  
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
    
    $descriptionRecordsCount = $this->_reader->readUInt16LE();
    for ($i = 0; $i < $descriptionRecordsCount; $i++) {
      $this->_reader->skip(2);
      $descriptionRecord = array("streamNumber" => $this->_reader->readUInt16LE());
      $nameLength = $this->_reader->readUInt16LE();
      $dataType = $this->_reader->readUInt16LE();
      $dataLength = $this->_reader->readUInt32LE();
      $descriptionRecord["name"] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16($nameLength));
      switch ($dataType) {
      case 0: // Unicode string
        $descriptionRecord["data"] = iconv
          ("utf-16le", $this->getOption("encoding"),
           $this->_reader->readString16($dataLength));
        break;
      case 1: // BYTE array
        $descriptionRecord["data"] = $this->_reader->read($dataLength);
        break;
      case 2: // BOOL
        $descriptionRecord["data"] = $this->_reader->readUInt16LE() == 1;
        break;
      case 3: // DWORD
        $descriptionRecord["data"] = $this->_reader->readUInt32LE();
        break;
      case 4: // QWORD
        $descriptionRecord["data"] = $this->_reader->readInt64LE();
        break;
      case 5: // WORD
        $descriptionRecord["data"] = $this->_reader->readUInt16LE();
        break;
      }
      $this->_descriptionRecords[] = $descriptionRecord;
    }
  }
  
  /**
   * Returns the array of description records. Each record consists of the
   * following keys.
   * 
   *   o streamNumber -- Specifies the stream number. Valid values are between
   *     1 and 127.
   *
   *   o name -- Specifies the name that uniquely identifies the attribute being
   *     described. Names are case-sensitive.
   *
   *   o data -- Specifies the actual metadata being stored.
   *
   * @return Array
   */
  public function getDescriptionRecords() { return $this->_descriptionRecords; }
  
  /**
   * Sets the array of description records. Each record must consist of the
   * following keys.
   * 
   *   o streamNumber -- Specifies the stream number. Valid values are between
   *     1 and 127.
   *
   *   o name -- Specifies the name that uniquely identifies the attribute being
   *     described. Names are case-sensitive.
   *
   *   o data -- Specifies the actual metadata being stored.
   *
   * @param Array $descriptionRecords The array of description records.
   */
  public function setDescriptionRecords($descriptionRecords)
  {
    $this->_descriptionRecords = $descriptionRecords;
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
      ($descriptionRecordsCount = count($this->_descriptionRecords));
    for ($i = 0; $i < $descriptionRecordsCount; $i++) {
      $data .= Transform::toUInt16LE(0) .
        Transform::toUInt16LE($this->_descriptionRecords[$i]["streamNumber"]) .
        Transform::toUInt16LE(strlen($name = iconv
          ($this->getOption("encoding"), "utf-16le",
           $this->_descriptionRecords[$i]["name"]) . "\0\0"));
      if (is_string($this->_descriptionRecords[$i]["data"])) {
        /* There is no way to distinguish byte arrays from unicode strings and
         * hence the need for a list of fields of type byte array */
        static $byteArray = array (
          ""
        ); // TODO: Add to the list if you encounter one

        if (in_array($name, $byteArray))
          $data .= Transform::toUInt16LE(1) . Transform::toUInt32LE
            (strlen($this->_descriptionRecords[$i]["data"])) . $name .
            $this->_descriptionRecords[$i]["data"];
        else {
          $value = iconv
            ($this->getOption("encoding"), "utf-16le",
             $this->_descriptionRecords[$i]["data"]);
          $value = ($value ? $value . "\0\0" : "");
          $data .= Transform::toUInt16LE(0) .
            Transform::toUInt32LE(strlen($value)) . $name .
            Transform::toString16($value);
        }
      }
      else if (is_bool($this->_descriptionRecords[$i]["data"])) {
        $data .= Transform::toUInt16LE(2) . Transform::toUInt32LE(2) . $name .
          Transform::toUInt16LE($this->_descriptionRecords[$i]["data"] ? 1 : 0);
      }
      else if (is_int($this->_descriptionRecords[$i]["data"])) {
        $data .= Transform::toUInt16LE(3) . Transform::toUInt32LE(4) . $name .
          Transform::toUInt32LE($this->_descriptionRecords[$i]["data"]);
      }
      else if (is_float($this->_descriptionRecords[$i]["data"])) {
        $data .= Transform::toUInt16LE(4) . Transform::toUInt32LE(8) . $name .
          Transform::toInt64LE($this->_descriptionRecords[$i]["data"]);
      }
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
