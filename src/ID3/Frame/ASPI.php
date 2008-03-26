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
 * @since      ID3v2.4.0
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
/**#@-*/

/**
 * Audio files with variable bit rates are intrinsically difficult to deal with
 * in the case of seeking within the file. The <i>Audio seek point index</i> or
 * ASPI frame makes seeking easier by providing a list a seek points within the
 * audio file. The seek points are a fractional offset within the audio data,
 * providing a starting point from which to find an appropriate point to start
 * decoding. The presence of an ASPI frame requires the existence of a
 * {@link ID3_Frame_TLEN} frame, indicating the duration of the file in
 * milliseconds. There may only be one audio seek point index frame in a tag.
 * 
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 * @since      ID3v2.4.0
 */
final class ID3_Frame_ASPI extends ID3_Frame
{
  /** @var integer */
  private $_dataStart;
  
  /** @var integer */
  private $_dataLength;
  
  /** @var integer */
  private $_size;
  
  /** @var Array */
  private $_fraction = array();
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_dataStart = Transform::fromInt32BE(substr($this->_data, 0, 4));
    $this->_dataLength = Transform::fromInt32BE(substr($this->_data, 4, 4));
    $this->_size = Transform::fromInt16BE(substr($this->_data, 8, 2));
    $bitsPerPoint = substr($this->_data, 10, 1);
    for ($i = 0, $offset = 11; $i < $this->_size; $i++) {
      if ($bitsPerPoint == 16) {
        $this->_fraction[$i] = substr($this->_data, $offset, 2);
        $offset += 2;
      } else {
        $this->_fraction[$i] = substr($this->_data, $offset, 1);
        $offset ++;
      }
    }
  }

  /**
   * Returns the byte offset from the beginning of the file.
   * 
   * @return integer
   */
  public function getDataStart() { return $this->_dataStart; }

  /**
   * Returns the byte length of the audio data being indexed.
   * 
   * @return integer
   */
  public function getDataLength() { return $this->_dataLength; }

  /**
   * Returns the number of index points in the frame.
   * 
   * @return integer
   */
  public function getSize() { return $this->_size; }

  /**
   * Returns the numerator of the fraction representing a relative position in
   * the data or <var>false</var> if index not defined. The denominator is 2
   * to the power of b.
   * 
   * @param integer $index The fraction numerator.
   * @return integer
   */
  public function getFractionAt($index) { return $this->_fraction[$index]; }
}
