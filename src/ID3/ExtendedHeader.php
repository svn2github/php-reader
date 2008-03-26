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
require_once("Object.php");
/**#@-*/

/**
 * The extended header contains information that can provide further insight in
 * the structure of the tag, but is not vital to the correct parsing of the tag
 * information; hence the extended header is optional.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ID3_ExtendedHeader extends ID3_Object
{
  /**
   * A flag to denote that the present tag is an update of a tag found earlier
   * in the present file or stream. If frames defined as unique are found in
   * the present tag, they are to override any corresponding ones found in the
   * earlier tag. This flag has no corresponding data.
   */
  const UPDATE = 128;
  
  /**
   * A flag to denote that a CRC-32 data is included in the extended header.
   * The CRC is calculated on all the data between the header and footer as
   * indicated by the header's tag length field, minus the extended header. Note
   * that this includes the padding (if there is any), but excludes the footer.
   * The CRC-32 is stored as an 35 bit synchsafe integer, leaving the upper four
   * bits always zeroed.
   */
  const CRC32 = 64;
  
  /** A flag to denote whether or not the tag has restrictions applied on it. */
  const RESTRICTED = 32;

  /** @var integer */
  private $_size;

  /** @var integer */
  private $_flags;
  
  /** @var integer */
  private $_crc;
  
  /** @var integer */
  private $_restrictions;
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ID3v2 tag.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $offset = $this->_reader->offset;
    $this->_size = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
    $this->_reader->skip(1);
    $this->_flags = $this->_reader->readInt8();
    
    if ($this->hasFlag(self::UPDATE))
      $this->_reader->skip(1);
    if ($this->hasFlag(self::CRC32)) {
      $this->_reader->skip(1);
      $this->_crc = Transform::getInt32BE
        (($this->_reader->read(1) << 4) &
         $this->decodeSynchsafe32($this->_reader->read(4)));
    }
    if ($this->hasFlag(self::RESTRICTED)) {
      $this->_reader->skip(1);
      $this->_restrictions = $this->_reader->readInt8(1);
    }
    
    $this->_reader->skip($this->_size - $this->_reader->offset - $offset);
  }
  
  /**
   * Returns the extended header size in bytes.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }
  
  /**
   * Checks whether or not the flag is set. Returns <var>true</var> if the flag
   * is set, <var>false</var> otherwise.
   * 
   * @param integer $flag The flag to query.
   * @return boolean
   */
  public function hasFlag($flag) { return ($this->_flags & $flag) == $flag; }
  
  /**
   * Returns the CRC-32 data.
   * 
   * @return integer
   */
  public function getCRC() { return $this->_crc; }
  
  /**
   * Returns the restrictions. For some applications it might be desired to
   * restrict a tag in more ways than imposed by the ID3v2 specification. Note
   * that the presence of these restrictions does not affect how the tag is
   * decoded, merely how it was restricted before encoding. If this flag is set
   * the tag is restricted as follows:
   *
   * <pre>
   * Restrictions %ppqrrstt
   *
   * p - Tag size restrictions
   *
   *   00   No more than 128 frames and 1 MB total tag size.
   *   01   No more than 64 frames and 128 KB total tag size.
   *   10   No more than 32 frames and 40 KB total tag size.
   *   11   No more than 32 frames and 4 KB total tag size.
   *
   * q - Text encoding restrictions
   *
   *   0    No restrictions
   *   1    Strings are only encoded with ISO-8859-1 or UTF-8.
   *
   * r - Text fields size restrictions
   *
   *   00   No restrictions
   *   01   No string is longer than 1024 characters.
   *   10   No string is longer than 128 characters.
   *   11   No string is longer than 30 characters.
   *
   *   Note that nothing is said about how many bytes is used to represent those
   *   characters, since it is encoding dependent. If a text frame consists of
   *   more than one string, the sum of the strungs is restricted as stated.
   *
   * s - Image encoding restrictions
   *
   *   0   No restrictions
   *   1   Images are encoded only with PNG [PNG] or JPEG [JFIF].
   *
   * t - Image size restrictions
   *
   *   00  No restrictions
   *   01  All images are 256x256 pixels or smaller.
   *   10  All images are 64x64 pixels or smaller.
   *   11  All images are exactly 64x64 pixels, unless required otherwise.
   * </pre>
   *
   * @return integer
   */
  public function getRestrictions() { return $this->_restrictions; }
}
