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
 * @subpackage ID3
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
require_once("ID3/Encoding.php");
/**#@-*/

/**
 * The <i>Attached picture</i> frame contains a picture directly related to the
 * audio file. Image format is the MIME type and subtype for the image.
 *
 * There may be several pictures attached to one file, each in their individual
 * APIC frame, but only one with the same content descriptor. There may only
 * be one picture with the same picture type. There is the possibility to put
 * only a link to the image file by using the MIME type "-->" and having a
 * complete URL instead of picture data.
 *
 * The use of linked files should however be used sparingly since there is the
 * risk of separation of files.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_APIC extends ID3_Frame
  implements ID3_Encoding
{
  /**
   * The list of image types.
   *
   * @var Array
   */
  public static $types = array
    ("Other", "32x32 pixels file icon (PNG only)", "Other file icon",
     "Cover (front)", "Cover (back)", "Leaflet page",
     "Media (e.g. label side of CD)", "Lead artist/lead performer/soloist",
     "Artist/performer", "Conductor", "Band/Orchestra", "Composer",
     "Lyricist/text writer", "Recording Location", "During recording",
     "During performance", "Movie/video screen capture",
     "A bright coloured fish", "Illustration", "Band/artist logotype",
     "Publisher/Studio logotype");
  
  /** @var integer */
  private $_encoding;
  
  /** @var string */
  private $_mimeType;
  
  /** @var integer */
  private $_imageType;
  
  /** @var string */
  private $_description;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_encoding = substr($this->_data, 0, 1);
    $this->_mimeType = substr
      ($this->_data, 1, ($pos = strpos($this->_data, "\0", 1)) - 1);
    $this->_pictureType = ord($this->_data{$pos++});
    $this->_data = substr($this->_data, $pos);
    
    switch ($this->_encoding) {
    case self::UTF16:
      list ($this->_description, $this->_data) =
        preg_split("/\\x00\\x00/", $this->_data, 2);
      $this->_description = Transform::fromString16($this->_description);
      break;
    case self::UTF16BE:
      list ($this->_description, $this->_data) =
        preg_split("/\\x00\\x00/", $this->_data, 2);
      $this->_description = Transform::fromString16BE($this->_description);
      break;
    default:
      list ($this->_description, $this->_data) =
        preg_split("/\\x00/", $this->_data, 2);
    }
  }
  
  /**
   * Returns the text encoding.
   * 
   * @return integer
   */
  public function getEncoding() { return $this->_encoding; }

  /**
   * Returns the MIME type. The MIME type is always encoded with ISO-8859-1.
   * 
   * @return string
   */
  public function getMimeType() { return $this->_mimeType; }

  /**
   * Returns the image type.
   * 
   * @return integer
   */
  public function getImageType() { return $this->_imageType; }

  /**
   * Returns the file description.
   * 
   * @return string
   */
  public function getDescription() { return $this->_description; }

  /**
   * Returns the embedded picture data.
   * 
   * @return string
   */
  public function getData() { return $this->_data; }
}
