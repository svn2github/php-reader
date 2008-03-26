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
/**#@-*/

/**
 * To increase performance and accuracy of jumps within a MPEG audio file,
 * frames with time codes in different locations in the file might be useful.
 * The <i>MPEG location lookup table</i> frame includes references that the
 * software can use to calculate positions in the file.
 *
 * The MPEG frames between reference describes how much the frame counter should
 * be increased for every reference. If this value is two then the first
 * reference points out the second frame, the 2nd reference the 4th frame, the
 * 3rd reference the 6th frame etc. In a similar way the bytes between reference
 * and milliseconds between reference points out bytes and milliseconds
 * respectively.
 *
 * Each reference consists of two parts; a certain number of bits that describes
 * the difference between what is said in bytes between reference and the
 * reality and a certain number of bits that describes the difference between
 * what is said in milliseconds between reference and the reality.
 *
 * There may only be one MLLT frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_MLLT extends ID3_Frame
{
  /** @var integer */
  private $_frames;
  
  /** @var integer */
  private $_bytes;

  /** @var integer */
  private $_milliseconds;
  
  /** @var Array */
  private $_deviation = array();
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_frames = Transform::fromInt16BE(substr($this->_data, 0, 2));
    $this->_bytes = Transform::fromInt32BE(substr($this->_data, 2, 3));
    $this->_milliseconds = Transform::fromInt32BE(substr($this->_data, 5, 3));
    
    $byteDevBits = ord(substr($this->_data, 8, 1));
    $millisDevBits = ord(substr($this->_data, 9, 1));
    
    $this->_data = substr($this->_data, 10); // FIXME: Better parsing of data
  }

  /**
   * Returns the number of MPEG frames between reference.
   * 
   * @return integer
   */
  public function getFrames() { return $this->_frames; }
  
  /**
   * Returns the number of bytes between reference.
   * 
   * @return integer
   */
  public function getBytes() { return $this->_bytes; }

  /**
   * Returns the number of milliseconds between references.
   * 
   * @return integer
   */
  public function getMilliseconds() { return $this->_milliseconds; }

  /**
   * Returns the deviations as an array. Each value is an array containing two
   * values, ie the deviation in bytes, and the deviation in milliseconds,
   * respectively.
   * 
   * @return Array
   */
  public function getDeviation() { return $this->_deviation; }
}
