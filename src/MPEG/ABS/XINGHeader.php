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
 * @subpackage MPEG
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id: XINGHeader.php 1 2008-07-06 10:43:41Z rbutterfield $
 */

/**#@+ @ignore */
require_once("Reader.php");
require_once("Twiddling.php");
require_once("MPEG/ABS/Object.php");
/**#@-*/

/**
 * This class represents the Xing VBR header which is often found in the first
 * frame of an MPEG Audio Bit Stream.
 *
 * @package    php-reader
 * @subpackage MPEG
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 1 $
 */
class MPEG_ABS_XINGHeader extends MPEG_ABS_Object
{
  /** @var integer */
  private $_frames = false;

  /** @var integer */
  private $_bytes = false;

  /** @var Array */
  private $_toc = array();

  /** @var integer */
  private $_qualityIndicator = false;

  /**
   * Constructs the class with given parameters and reads object related data
   * from the bitstream.
   *
   * @param Reader $reader The reader object.
   * @param Array $options Array of options.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $flags = $reader->readUInt32BE();
    
    if (Twiddling::testAnyBits($flags, 0x1))
      $this->_frames = $this->_reader->readUInt32BE();
    if (Twiddling::testAnyBits($flags, 0x2))
      $this->_bytes = $this->_reader->readUInt32BE();
    if (Twiddling::testAnyBits($flags, 0x4))
      $this->_toc = array_merge(unpack("C*", $this->_reader->read(100)));
    if (Twiddling::testAnyBits($flags, 0x8))
      $this->_qualityIndicator = $this->_reader->readUInt32BE();
  }
  
  /**
   * Returns the number of frames in the file.
   *
   * @return integer
   */
  public function getFrames() { return $this->_frames; }

  /**
   * Returns the number of bytes in the file.
   *
   * @return integer
   */
  public function getBytes() { return $this->_bytes; }

  /**
   * Returns the table of contents array. The returned array has a fixed amount
   * of 100 seek points to the file.
   *
   * @return Array
   */
  public function getToc() { return $this->_toc; }

  /**
   * Returns the quality indicator. The indicator is from 0 (best quality) to
   * 100 (worst quality).
   *
   * @return integer
   */
  public function getQualityIndicator() { return $this->_qualityIndicator; }

  /**
   * Returns the length of the header in bytes.
   *
   * @return integer
   */
  public function getLength()
  {
    return 4 +
      ($this->_frames !== false ? 4 : 0) +
      ($this->_bytes !== false ? 4 : 0) +
      (empty($this->_toc) ? 0 : 100) +
      ($this->_qualityIndicator !== false ? 4 : 0);
  }
}
