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
 * @version    $Id: MPEG.php 1 2008-07-06 10:43:41Z rbutterfield $
 */

/**#@+ @ignore */
require_once("Twiddling.php");
require_once("MPEG/Object.php");
/**#@-*/

/**
 * This class represents a MPEG Program Stream encoded file as described in
 * MPEG-1 Systems (ISO/IEC 11172-1) and MPEG-2 Systems (ISO/IEC 13818-1)
 * standards.
 * 
 * The Program Stream is a stream definition which is tailored for communicating
 * or storing one program of coded data and other data in environments where
 * errors are very unlikely, and where processing of system coding, e.g. by
 * software, is a major consideration.
 * 
 * This class only supports the parsing of the play duration.
 * 
 * @package    php-reader
 * @subpackage MPEG
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 1 $
 * @todo       Full implementation
 */
final class MPEG_PS extends MPEG_Object
{
  /** @var integer */
  private $_length;
  
  /**
   * Constructs the class with given file and options.
   *
   * @param string|Reader $filename The path to the file, file descriptor of an
   *                                opened file, or {@link Reader} instance.
   * @param Array         $options  The options array.
   */
  public function __construct($filename, $options = array())
  {
    if ($filename instanceof Reader)
      $reader = &$filename;
    else
      $reader = new Reader($filename);
    
    parent::__construct($reader, $options);
    
    $startCode = 0; $startTime = 0;
    $pictureCount = 0; $pictureRate = 0;
    $rates = array ( 0, 23.976, 24, 25, 29.97, 30, 50, 59.94, 60 );
    $foundSeqHdr = false; $foundGOP = false;
    
    do {
      do {
        $startCode = $this->nextStartCode();
      } while ($startCode != 0x1b3 && $startCode != 0x1b8);
      if ($startCode == 0x1b3 /* sequence_header_code */ && $pictureRate == 0) {
        $i1 = $this->_reader->readUInt32BE();
        $i2 = $this->_reader->readUInt32BE();
        if (!Twiddling::testAllBits($i2, 0x2000))
          throw new RuntimeException("Invalid mark");
        $pictureRate = $rates[Twiddling::getValue($i1, 4, 8)];
        $foundSeqHdr = true;
      }
      if ($startCode == 0x1b8 /* group_start_code */) {
        $tmp = $this->_reader->readUInt32BE();
        $startTime = (($tmp >> 26) & 0x1f) * 60 * 60 * 1000 /* hours */ +
                (($tmp >> 20) & 0x3f) * 60 * 1000 /* minutes */ +
                (($tmp >> 13) & 0x3f) * 1000 /* seconds */ +
                (int)(1 / $pictureRate * (($tmp >> 7) & 0x3f) * 1000);
        $foundGOP = true;
      }
    } while (!$foundSeqHdr || !$foundGOP);
    
    $this->_reader->setOffset($this->_reader->getSize());
    
    do {
      if (($startCode = $this->prevStartCode()) == 0x100)
        $pictureCount++;
    } while ($startCode != 0x1b8);
    
    $this->_reader->skip(4);
    $tmp = $this->_reader->readUInt32BE();
    $this->_length =
      (((($tmp >> 26) & 0x1f) * 60 * 60 * 1000 /* hours */ +
        (($tmp >> 20) & 0x3f) * 60 * 1000 /* minutes */ +
        (($tmp >> 13) & 0x3f) * 1000 /* seconds */ +
        (int)(1 / $pictureRate * (($tmp >> 7) & 0x3f) * 1000)) - $startTime +
       (int)(1 / $pictureRate * $pictureCount * 1000)) / 1000;
  }
  
  /**
   * Returns the exact playtime in seconds.
   *
   * @return integer
   */
  public function getLength() { return $this->_length; }
  
  /**
   * Returns the exact playtime given in seconds as a string in the form of
   * [hours:]minutes:seconds.milliseconds.
   *
   * @param integer $seconds The playtime in seconds.
   * @return string
   */
  public function getFormattedLength()
  {
    return $this->formatTime($this->getLength());
  }
}
