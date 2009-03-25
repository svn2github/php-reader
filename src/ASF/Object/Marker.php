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
 * The <i>Marker Object</i> class.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_Marker extends ASF_Object
{
  
  /** @var string */
  private $_reserved1;
  
  /** @var integer */
  private $_reserved2;
  
  /** @var string */
  private $_name;

  /** @var Array */
  private $_markers = array();
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_reserved1 = $this->_reader->readGUID();
    $markersCount = $this->_reader->readUInt32LE();
    $this->_reserved2 = $this->_reader->readUInt16LE();
    $nameLength = $this->_reader->readUInt16LE();
    $this->_name = iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16($nameLength));
    for ($i = 0; $i < $markersCount; $i++) {
      $marker = array
        ("offset" => $this->_reader->readInt64LE(),
         "presentationTime" => $this->_reader->readInt64LE());
      $this->_reader->skip(2);
      $marker["sendTime"] = $this->_reader->readUInt32LE();
      $marker["flags"] = $this->_reader->readUInt32LE();
      $descriptionLength = $this->_reader->readUInt32LE();
      $marker["description"] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16($descriptionLength));
      $this->_markers[] = $marker;
    }
  }

  /**
   * Returns the name of the Marker Object.
   *
   * @return Array
   */
  public function getName() { return $this->_name; }

  /**
   * Returns the name of the Marker Object.
   * 
   * @param string $name The name.
   */
  public function setName($name) { $this->_name = $name; }
  
  /**
   * Returns an array of markers. Each entry consists of the following keys.
   * 
   *   o offset -- Specifies a byte offset into the <i>Data Object</i> to the
   *     actual position of the marker in the <i>Data Object</i>. ASF parsers
   *     must seek to this position to properly display data at the specified
   *     marker <i>Presentation Time</i>.
   * 
   *   o presentationTime -- Specifies the presentation time of the marker, in
   *     100-nanosecond units.
   * 
   *   o sendTime -- Specifies the send time of the marker entry, in
   *     milliseconds.
   * 
   *   o flags -- Flags are reserved and should be set to 0.
   * 
   *   o description -- Specifies a description of the marker entry.
   *
   * @return Array
   */
  public function getMarkers() { return $this->_markers; }
  
  /**
   * Sets the array of markers. Each entry is to consist of the following keys.
   * 
   *   o offset -- Specifies a byte offset into the <i>Data Object</i> to the
   *     actual position of the marker in the <i>Data Object</i>. ASF parsers
   *     must seek to this position to properly display data at the specified
   *     marker <i>Presentation Time</i>.
   * 
   *   o presentationTime -- Specifies the presentation time of the marker, in
   *     100-nanosecond units.
   * 
   *   o sendTime -- Specifies the send time of the marker entry, in
   *     milliseconds.
   * 
   *   o flags -- Flags are reserved and should be set to 0.
   * 
   *   o description -- Specifies a description of the marker entry.
   * 
   * @param Array $markers The array of markers.
   */
  public function setMarkers($markers) { $this->_markers = $markers; }
  
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
    $data =
      Transform::toGUID($this->_reserved1) .
      Transform::toUInt32LE($markersCount = count($this->_markers)) .
      Transform::toUInt16LE($this->_reserved2) .
      Transform::toUInt16LE
        (strlen($name = iconv
         ($this->getOption("encoding"), "utf-16le", $this->_name) . "\0\0")) .
      Transform::toString16($name);
    for ($i = 0; $i < $markersCount; $i++)
      $data .=
        Transform::toInt64LE($this->_markers[$i]["offset"]) .
        Transform::toInt64LE($this->_markers[$i]["presentationTime"]) .
        Transform::toUInt16LE
          (12 + ($descriptionLength = strlen($description = iconv
           ("utf-16le", $this->getOption("encoding"),
            $this->_markers[$i]["description"]) . "\0\0"))) .
        Transform::toUInt32LE($this->_markers[$i]["sendTime"]) .
        Transform::toUInt32LE($this->_markers[$i]["flags"]) .
        Transform::toUInt32LE($descriptionLength) .
        Transform::toString16($description);
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
