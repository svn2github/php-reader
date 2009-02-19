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
require_once("ASF/Object/Container.php");
/**#@-*/

/**
 * The <i>Header Extension Object</i> allows additional functionality to be
 * added to an ASF file while maintaining backward compatibility. The Header
 * Extension Object is a container containing zero or more additional extended
 * header objects.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_HeaderExtension extends ASF_Object_Container
{
  /** @var string */
  private $_reserved1;
  
  /** @var integer */
  private $_reserved2;
  
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
    $this->_reserved2 = $this->_reader->readUInt16LE();
    $this->_reader->skip(4);
    $this->constructObjects
      (array
       (self::EXTENDED_STREAM_PROPERTIES => "ExtendedStreamProperties",
        self::ADVANCED_MUTUAL_EXCLUSION => "AdvancedMutualExclusion",
        self::GROUP_MUTUAL_EXCLUSION => "GroupMutualExclusion",
        self::STREAM_PRIORITIZATION  => "StreamPrioritization",
        self::BANDWIDTH_SHARING  => "BandwidthSharing",
        self::LANGUAGE_LIST  => "LanguageList",
        self::METADATA  => "Metadata",
        self::METADATA_LIBRARY => "MetadataLibrary",
        self::INDEX_PARAMETERS  => "IndexParameters",
        self::MEDIA_OBJECT_INDEX_PARAMETERS => "MediaObjectIndexParameters",
        self::TIMECODE_INDEX_PARAMETERS => "TimecodeIndexParameters",
        self::COMPATIBILITY => "Compatibility",
        self::ADVANCED_CONTENT_ENCRYPTION => "AdvancedContentEncryption",
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
      (24 /* for header */ + 22 + strlen($data) /* for object data */);
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize()) .
      Transform::toGUID($this->_reserved1) .
      Transform::toUInt16LE($this->_reserved2) .
      Transform::toUInt32LE(strlen($data)) . $data;
  }
}
