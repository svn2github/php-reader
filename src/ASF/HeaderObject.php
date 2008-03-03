<?php
/**
 * $Id$
 *
 *
 * Copyright (C) 2006, 2007 The Bearpaw Project Work Group. All Rights Reserved.
 * Copyright (C) 2007, 2008 BEHR Software Systems. All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the BEHR Software Systems nor the names of its
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
 * @package   php-reader
 */

/**#@+ @ignore */
require_once("Object.php");
/**#@-*/

/**
 * The ASF_Header_Object object implementation. This object contains objects
 * that give information about the file. See corresponding object classes for
 * more.
 * 
 * @package   php-reader
 * @author    Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright 2006, 2007 The Bearpaw Project Work Group
 * @copyright 2007, 2008 BEHR Software Systems
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   $Rev$
 */
final class ASF_HeaderObject extends ASF_Object
{
  /**
   * @var integer The number of the objects the header contains.
   */
  private $_objectCount;

  /**
   * @var integer Internal variable to have the start of the stream stored in.
   */
  private $_readerSOffset;

  /**
   * @var integer Internal variable to have the current position of the
   *      stream pointer stored in.
   */
  private $_readerCOffset;

  /**
   * Default constructor. Initiates the class with given parameters and reads
   * object information from the file.
   */
  public function __construct($reader, $id, $size)
  {
    parent::__construct($reader, $id, $size);

    $this->_readerSOffset = $this->_reader->getOffset();
    $this->_objectCount = $this->_reader->getUInt32LE();
    $this->_reader->skip(2);
    $this->_readerCOffset = $this->_reader->getOffset();
  }
  
  /**
   * Returns the number of standard ASF header objects this object contains.
   * 
   * @return integer The number of objects the header contains.
   */
  public function getObjectCount() { return $this->_objectCount; }

  /**
   * Checks whether there is more to be read within the bounds of the parent
   * object size.
   *
   * @return boolean Boolean value corresponding whether there is more to read.
   */
  public function hasChildObjects()
  {
    return ($this->_readerSOffset + $this->_size) > $this->_readerCOffset;
  }

  /**
   * Returns the next standard ASF object or <var>false</var> if end of stream
   * has been reached.
   *
   * @todo   Only limited subset of possible child objects are regognized.
   * @return ASF_Object Returns the appropriate object. Returned objects are of
   *         the type ASFObject or of any of the other object types that inherit
   *         from that base class.
   */
  public function nextChildObject()
  {
    $object = false;
    if ($this->hasChildObjects()) {
      $this->_reader->setOffset($this->_readerCOffset);
      $guid = $this->_reader->getGUID();
      $size = $this->_reader->getInt64LE();
      $offset = $this->_reader->getOffset();
      switch ($guid) {
      /* ASF_Content_Description_Object */
      case "75b22633-668e-11cf-a6d9-00aa0062ce6c":
        $object =
          new ASF_ContentDescriptionObject($this->_reader, $guid, $size);
        break;
      /* ASF_Header_Extension_Object */
      case "5fbf03b5-a92e-11cf-8ee3-00c00c205365":
        $this->_reader->skip(48);
        $this->_readerCOffset = $this->_reader->getOffset();
        $object = $this->nextChildObject();
        break;
      /* ASF_Extended_Content_Description_Object */
      case "d2d0a440-e307-11d2-97f0-00a0c95ea850":
        $object = new ASF_ExtendedContentDescriptionObject
          ($this->_reader, $guid, $size);
        break;
      /* ASF_File_Properties_Object */
      case "8cabdca1-a947-11cf-8ee4-00c00c205365":
        $object = new ASF_FilePropertiesObject($this->_reader, $guid, $size);
        break;
      default:  // not implemented
        $object = new ASF_Object($this->_reader, $guid, $size);
      }
      $this->_reader->setOffset(($this->_readerCOffset = $offset - 24 + $size));
    }
    return $object;
  }
}
