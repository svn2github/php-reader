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
require_once("ID3/Frame.php");
/**#@-*/

/**
 * The <i>Equalisation (2)</i> is another subjective, alignment frame. It allows
 * the user to predefine an equalisation curve within the audio file. There may
 * be more than one EQU2 frame in each tag, but only one with the same
 * identification string.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_EQU2 extends ID3_Frame
{
  /**
   * Interpolation type that defines that no interpolation is made. A jump from
   * one adjustment level to another occurs in the middle between two adjustment
   * points.
   */
  const BAND = 0x00;
  
  /**
   * Interpolation type that defines that interpolation between adjustment
   * points is linear.
   */
  const LINEAR = 0x01;

  /** @var integer */
  private $_interpolation;
  
  /** @var string */
  private $_device;
  
  /** @var Array */
  private $_adjustments;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_interpolation = substr($this->_data, 0, 1);
    list ($this->_device, $this->_data) =
      preg_split("/\\x00/", substr($this->_data, 1), 2);
    
    for ($i = 0; $i < strlen($this->_data); $i += 8)
      $this->_adjustments[Transform::getInt16BE(substr($this->_data, $j, 2))] = 
        Transform::getInt16BE(substr($this->_data, $j + 2, 2));
    sort($this->_adjustments);
  }

  /**
   * Returns the interpolation method. The interpolation method describes which
   * method is preferred when an interpolation between the adjustment point that
   * follows.
   *
   * @return integer
   */
  public function getInterpolation() { return $this->_interpolation; }

  /**
   * Returns the device where the adjustments should apply.
   *
   * @return string
   */
  public function getDevice() { return $this->_device; }
   
  /**
   * Returns the array containing adjustments having fequencies as keys and
   * their corresponding adjustments as values.
   *
   * The frequency is stored in units of 1/2 Hz, giving it a range from 0 to
   * 32767 Hz.
   *
   * The volume adjustment is encoded as a fixed point decibel value, 16 bit
   * signed integer representing (adjustment*512), giving +/- 64 dB with a
   * precision of 0.001953125 dB. E.g. +2 dB is stored as $04 00 and -2 dB is
   * $FC 00.
   *
   * Adjustment points are ordered by frequency and one frequency is described
   * once in the frame.
   * 
   * @return Array
   */
  public function getAdjustments() { return $this->_adjustments; }
}
