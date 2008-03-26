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
 * A base class for all ID3v2 frames as described in the
 * {@link http://www.id3.org/id3v2.4.0-frames ID3v2 frames document}.
 *
 * 
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
class ID3_Frame extends ID3_Object
{
  /**
   * This flag tells the tag parser what to do with this frame if it is unknown
   * and the tag is altered in any way. This applies to all kinds of
   * alterations, including adding more padding and reordering the frames.
   */
  const DISCARD_ON_TAGCHANGE = 16384;
  
  /**
   * This flag tells the tag parser what to do with this frame if it is unknown
   * and the file, excluding the tag, is altered. This does not apply when the
   * audio is completely replaced with other audio data.
   */
  const DISCARD_ON_FILECHANGE = 8192;
  
  /**
   * This flag, if set, tells the software that the contents of this frame are
   * intended to be read only. Changing the contents might break something,
   * e.g. a signature.
   */
  const READ_ONLY = 4096;
  
  /**
   * This flag indicates whether or not this frame belongs in a group with
   * other frames. If set, a group identifier byte is added to the frame. Every
   * frame with the same group identifier belongs to the same group.
   */
  const GROUPING_IDENTITY = 32;
  
  /**
   * This flag indicates whether or not the frame is compressed. A <i>Data
   * Length Indicator</i> byte is included in the frame.
   *
   * @see DATA_LENGTH_INDICATOR
   */
  const COMPRESSION = 8;
  
  /**
   * This flag indicates whether or not the frame is encrypted. If set, one byte
   * indicating with which method it was encrypted will be added to the frame.
   * See description of the {@link ID3_Frame_ENCR} frame for more information
   * about encryption method registration. Encryption should be done after
   * compression. Whether or not setting this flag requires the presence of a
   * <i>Data Length Indicator</i> depends on the specific algorithm used.
   *
   * @see DATA_LENGTH_INDICATOR
   */
  const ENCRYPTION = 4;
  
  /**
   * This flag indicates whether or not unsynchronisation was applied to this
   * frame.
   */
  const UNSYNCHRONISATION = 2;
  
  /**
   * This flag indicates that a data length indicator has been added to the
   * frame.
   */
  const DATA_LENGTH_INDICATOR = 1;

  /** @var integer */
  private $_identifier;

  /** @var integer */
  private $_size;

  /** @var integer */
  private $_flags;
  
  /**
   * Raw content read from the frame.
   * 
   * @var string
   */
  protected $_data;
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ID3v2 tag.
   *
   * @todo  Only limited subset of flags are processed.
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_identifier = $this->_reader->readString8(4);
    $this->_size = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
    $this->_flags = $this->_reader->readUInt16BE();
    $this->_data = $this->_reader->read($this->_size);
  }
  
  /**
   * Returns the frame identifier string.
   * 
   * @return string
   */
  public function getIdentifier() { return $this->_identifier; }
  
  /**
   * Sets the frame identifier.
   * 
   * @param string $identifier The identifier.
   */
  public function setIdentifier($identifier)
  {
    $this->_identifier = $identifier;
  }
  
  /**
   * Returns the size of the data in the final frame, after encryption,
   * compression and unsynchronisation. The size is excluding the frame header.
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
   * Returns the frame flags byte.
   * 
   * @return integer
   */
  public function getFlags($flags)
  {
    return $this->_flags;
  }
  
  /**
   * Sets the frame flags byte.
   * 
   * @param string $flags The flags byte.
   */
  public function setFlags($flags)
  {
    $this->_flags = $flags;
  }
}
