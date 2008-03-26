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
 * The <i>Relative volume adjustment (2)</i> frame is a more subjective frame than
 * the previous ones. It allows the user to say how much he wants to
 * increase/decrease the volume on each channel when the file is played. The
 * purpose is to be able to align all files to a reference volume, so that you
 * don't have to change the volume constantly. This frame may also be used to
 * balance adjust the audio. The volume adjustment is encoded as a fixed point
 * decibel value, 16 bit signed integer representing (adjustment*512), giving
 * +/- 64 dB with a precision of 0.001953125 dB. E.g. +2 dB is stored as $04 00
 * and -2 dB is $FC 00.
 *
 * There may be more than one RVA2 frame in each tag, but only one with the same
 * identification string.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 * @since      ID3v2.4.0
 */
final class ID3_Frame_RVA2 extends ID3_Frame
{
  /**
   * The list of channel types.
   *
   * @var Array
   */
  public static $types = array
    (0x00 => "Other",
     0x01 => "Master volume",
     0x02 => "Front right",
     0x03 => "Front left",
     0x04 => "Back right",
     0x05 => "Back left",
     0x06 => "Front centre",
     0x07 => "Back centre",
     0x08 => "Subwoofer");
  
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

    list ($this->_device, $this->_data) =
      preg_split("/\\x00/", $this->_data, 2);
    
    for ($i = $j = 0; $i < 9; $i++) {
      $this->_adjustments[$i] = array
        ("channelType" => substr($this->_data, $j++, 1),
         "volumeAdjustment" =>
           Transform::fromInt16BE(substr($this->_data, $j++, 2)));
      $bitsInPeak = ord(substr($this->_data, (++$j)++, 1));
      $bytesInPeak = $bitsInPeak > 0 ? ceil($bitsInPeak / 8) : 0;
      switch ($bytesInPeak) {
      case 32:
      case 24:
        $this->_adjustments[$i]["peakVolume"] =
          Transform::fromInt32BE(substr($this->_data, $j, $bytesInPeak));
        $j += $bytesInPeak;
        break;
      case 16:
        $this->_adjustments[$i]["peakVolume"] =
          Transform::fromInt16BE(substr($this->_data, $j, $bytesInPeak));
        $j += $bytesInPeak;
        break;
      case 8:
        $this->_adjustments[$i]["peakVolume"] =
          Transform::fromInt8(substr($this->_data, $j, $bytesInPeak));
        $j += $bytesInPeak;
      }
    }
  }

  /**
   * Returns the device where the adjustments should apply.
   *
   * @return string
   */
  public function getDevice() { return $this->_device; }
   
  /**
   * Returns the array containing volume adjustments for each channel. Volume
   * adjustments are arrays themselves containing the following keys:
   * channelType, volumeAdjustment, peakVolume.
   * 
   * @return Array
   */
  public function getAdjustments() { return $this->_adjustments; }
}
