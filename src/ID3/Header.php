<?php
/**
 * PHP Reader Library
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
 * @package    php-reader
 * @subpackage ID3
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("Object.php");
/**#@-*/

/**
 * The first part of the ID3v2 tag is the 10 byte tag header. The first three
 * bytes of the tag are always "ID3", to indicate that this is an ID3v2 tag,
 * directly followed by the two version bytes. The first byte of ID3v2 version
 * is its major version, while the second byte is its revision number. All
 * revisions are backwards compatible while major versions are not. The version
 * is followed by the ID3v2 flags field, of which currently four flags are used.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev$
 */
final class ID3_Header extends ID3_Object
{
  /** A flag to denote whether or not unsynchronisation is applied on all
      frames */
  const UNSYNCHRONISATION = 256;
  
  /** A flag to denote whether or not the header is followed by an extended
      header */
  const EXTENDEDHEADER = 128;
  
  /** A flag used as an experimental indicator. This flag shall always be set
      when the tag is in an experimental stage. */
  const EXPERIMENTAL = 64;
  
  /** A flag to denote whether a footer is present at the very end of the tag */
  const FOOTER = 32;

  /** @var integer */
  private $_version;
  
  /** @var integer */
  private $_revision;
  
  /** @var integer */
  private $_flags;
  
  /** @var integer */
  private $_size;

  /**
   * Constructs the class with given parameters and reads object related data
   * from the ID3v2 tag.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_version = ord($this->_reader->getInt8());
    $this->_revision = ord($this->_reader->getInt8());
    $this->_flags = $this->_reader->getInt8();
    $this->_size = $this->decodeSynchsafe32($this->_reader->getUInt32BE());
  }
  
  /**
   * Returns the tag major version number.
   * 
   * @return integer
   */
  public function getVersion() { return $this->_version; }

  /**
   * Returns the tag revision number.
   * 
   * @return integer
   */
  public function getRevision() { return $this->_revision; }
  
  /**
   * Checks whether or not the flag is set. Returns <var>true</var> if the flag
   * is set, <var>false</var> otherwise.
   * 
   * @param integer $flag The flag to query.
   * @return boolean
   */
  public function hasFlag($flag) { return ($this->_flags & $flag) == $flag; }
  
  /**
   * Returns the tag size, excluding the header and the footer.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }
}
