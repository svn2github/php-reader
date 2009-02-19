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
require_once("ASF/Object/Container.php");
/**#@-*/

/**
 * The role of the header object is to provide a well-known byte sequence at the
 * beginning of ASF files and to contain all the information that is needed to
 * properly interpret the information within the data object. The header object
 * can optionally contain metadata such as bibliographic information.
 *
 * Of the three top-level ASF objects, the header object is the only one that
 * contains other ASF objects. The header object may include a number of
 * standard objects including, but not limited to:
 *
 *  o File Properties Object -- Contains global file attributes.
 *  o Stream Properties Object -- Defines a digital media stream and its
 *    characteristics.
 *  o Header Extension Object -- Allows additional functionality to be added to
 *    an ASF file while maintaining backward compatibility.
 *  o Content Description Object -- Contains bibliographic information.
 *  o Script Command Object -- Contains commands that can be executed on the
 *    playback timeline.
 *  o Marker Object -- Provides named jump points within a file.
 *
 * Note that objects in the header object may appear in any order. To be valid,
 * the header object must contain a {@link ASF_Object_FileProperties File
 * Properties Object}, a {@link ASF_Object_HeaderExtension Header Extension
 * Object}, and at least one {@link ASF_Object_StreamProperties Stream
 * Properties Object}.
 * 
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_Header extends ASF_Object_Container
{
  /** @var integer */
  private $_reserved1;
  
  /** @var integer */
  private $_reserved2;
  
  /**
   * Constructs the class with given parameters and options.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_reader->skip(4);
    $this->_reserved1 = $this->_reader->readInt8();
    $this->_reserved2 = $this->_reader->readInt8();
    $this->constructObjects
      (array
       (self::FILE_PROPERTIES => "FileProperties",
        self::STREAM_PROPERTIES => "StreamProperties",
        self::HEADER_EXTENSION => "HeaderExtension",
        self::CODEC_LIST => "CodecList",
        self::SCRIPT_COMMAND => "ScriptCommand",
        self::MARKER => "Marker",
        self::BITRATE_MUTUAL_EXCLUSION => "BitrateMutualExclusion",
        self::ERROR_CORRECTION => "ErrorCorrection",
        self::CONTENT_DESCRIPTION => "ContentDescription",
        self::EXTENDED_CONTENT_DESCRIPTION => "ExtendedContentDescription",
        self::CONTENT_BRANDING => "ContentBranding",
        self::STREAM_BITRATE_PROPERTIES => "StreamBitrateProperties",
        self::CONTENT_ENCRYPTION => "ContentEncryption",
        self::EXTENDED_CONTENT_ENCRYPTION => "ExtendedContentEncryption",
        self::DIGITAL_SIGNATURE => "DigitalSignature",
        self::PADDING => "Padding"));
  }
  
  /**
   * Returns the whether the object is required to be present, or whether
   * minimum cardinality is 1.
   * 
   * @return boolean
   */
  public function isMandatory() { return true; }
  
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
    $data = "";
    foreach ($this->getObjects() as $objects)
      foreach ($objects as $object)
        $data .= $object->__toString();
    $this->setSize
      (24 /* for header */ + 6 + strlen($data) /* for object data */);
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize()) .
      Transform::toUInt32LE(count($this->getObjects())) .
      Transform::toInt8($this->_reserved1) .
      Transform::toInt8($this->_reserved2) . $data;
  }
}
