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
require_once("MPEG/Audio/Object.php");
require_once("MPEG/Audio/Frame.php");
/**#@-*/

/**
 * This class represents an MPEG Audio file as described in ISO/IEC 11172-3 and
 * ISO/IEC 13818-3 standards.
 * 
 * Non-standard VBR header extensions or namely XING, VBRI and LAME headers are
 * supported.
 * 
 * This class is optimized for fast determination of the play duration of the
 * file and hence uses lazy data reading mode by default. In this mode the
 * actual frames and frame data are only read when referenced directly. You may
 * change this behaviour by giving an appropriate option to the constructor.
 * 
 * @package    php-reader
 * @subpackage MPEG
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 1 $
 */
final class MPEG_Audio extends MPEG_Audio_Object
{
  /** @var integer */
  private $_bytes;
  
  /** @var Array */
  private $_frames = array();
  
  /** @var MPEG_Audio_XINGHeader */
  private $_xingHeader = null;

  /** @var MPEG_Audio_LAMEHeader */
  private $_lameHeader = null;

  /** @var MPEG_Audio_VBRIHeader */
  private $_vbriHeader = null;
  
  /** @var integer */
  private $_cumulativeBitrate = 0;
  
  /** @var integer */
  private $_cumulativePlayDuration = 0;
  
  /** @var integer */
  private $_estimatedBitrate = 0;
  
  /** @var integer */
  private $_estimatedPlayDuration = 0;
  
  /** @var integer */
  private $_lastFrameOffset = false;
  
  
  /**
   * Constructs the MPEG_Audio class with given file and options.
   *
   * The following options are currently recognized:
   *   o readmode -- Can be one of full or lazy and determines when the read of
   *     frames and their data happens. When in full mode the data is read
   *     automatically during the instantiation of the frame and all the frames
   *     are read during the instantiation of this class. While this allows
   *     faster validation and data fetching, it is unnecessary in terms of
   *     determining just the play duration of the file. Defaults to lazy.
   * 
   *   o estimatePrecision -- Only applicaple with lazy read mode to determine
   *     the precision of play duration estimate. This precision is equal to how
   *     many frames are read before fixing the average bitrate that is used to
   *     calculate the play duration estimate of the whole file. Each frame adds
   *     about 0.1-0.2ms to the processing of the file. Defaults to 1000.
   * 
   * When in lazy data reading mode it is first checked whether a VBR header is
   * found in a file. If so, the play duration is calculated based no its data
   * and no further frames are read from the file. If no VBR header is found,
   * frames up to estimatePrecision are read to calculate an average bitrate.
   * 
   * Hence, only zero or <var>estimatePrecision</var> number of frames are read
   * in lazy data reading mode. The rest of the frames are read automatically
   * when directly referenced, ie the data is read when it is needed.
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
    
    $offset = $this->_reader->getOffset();
    $this->_bytes = $this->_reader->getSize();
    
    /* Skip ID3v1 tag */
    $this->_reader->setOffset(-128);
    if ($this->_reader->read(3) == "TAG")
      $this->_bytes -= 128;
    $this->_reader->setOffset($offset);

    /* Skip ID3v2 tag */
    if ($this->_reader->readString8(3) == "ID3") {
      require_once("ID3/Header.php");
      $header = new ID3_Header($this->_reader);
      $this->_reader->skip
        ($header->getSize() + ($header->hasFlag(ID3_Header::FOOTER) ? 10 : 0));
    }
    else
      $this->_reader->setOffset($offset);
    

    $offset = $this->_reader->getOffset();
    
    /* Check for VBR headers */
    $firstFrame = new MPEG_Audio_Frame($this->_reader, $options);
    
    $this->_reader->setOffset
      ($offset + 4 + self::$sidesizes
       [$firstFrame->getFrequencyType()][$firstFrame->getMode()]);
    if (($xing = $this->_reader->readString8(4)) == "Xing" || $xing == "Info") {
      require_once("MPEG/Audio/XINGHeader.php");
      $this->_xingHeader = new MPEG_Audio_XINGHeader($this->_reader, $options);
      if ($this->_reader->readString8(4) == "LAME") {
        require_once("MPEG/Audio/LAMEHeader.php");
        $this->_lameHeader =
          new MPEG_Audio_LAMEHeader($this->_reader, $options);
      }
    }
    
    $this->_reader->setOffset($offset + 4 + 32);
    if ($this->_reader->readString8(4) == "VBRI") {
      require_once("MPEG/Audio/VBRIHeader.php");
      $this->_vbriHeader = new MPEG_Audio_VBRIHeader($this->_reader, $options);
    }
    
    $this->_reader->setOffset($offset);
    
    /* Read necessary frames */
    if ($this->getOption("readmode", "lazy") == "lazy") {
      if (($header = $this->_xingHeader) !== null ||
          ($header = $this->_vbriHeader) !== null) {
        $this->_estimatedPlayDuration = $header->getFrames() *
          $firstFrame->getSamples() / $firstFrame->getSamplingFrequency();
        if ($this->_lameHeader !== null) {
          $this->_estimatedBitrate = $this->_lameHeader->getBitrate();
          if ($this->_estimatedBitrate == 255)
            $this->_estimatedBitrate = round
              (($this->_lameHeader->getMusicLength()) /
               (($header->getFrames() * $firstFrame->getSamples()) /
                $firstFrame->getSamplingFrequency()) / 1000 * 8);
        }
        else
          $this->_estimatedBitrate = ($this->_bytes - $offset) /
            $this->_estimatedPlayDuration / 1000 * 8;
      }
      else {
        $this->_readFrames($this->getOption("estimatePrecision", 1000));
        
        $this->_estimatedBitrate =
          $this->_cumulativeBitrate / count($this->_frames);
        $this->_estimatedPlayDuration =
          ($this->_bytes - $offset) / ($this->_estimatedBitrate * 1000 / 8);
      }
    }
    else {
      $this->_readFrames();
      
      $this->_estimatedBitrate =
        $this->_cumulativeBitrate / count($this->_frames);
      $this->_estimatedPlayDuration = $this->_cumulativePlayDuration;
    }
  }
  
  /**
   * Returns the bitrate estimate. This value is either fetched from one of the
   * headers or calculated based on the read frames.
   * 
   * @return integer
   */
  public function getBitrateEstimate()
  {
    return $this->_estimatedBitrate;
  }
  
  /**
   * For variable bitrate files this method returns the exact average bitrate of
   * the whole file.
   * 
   * @return integer
   */
  public function getBitrate()
  {
    if ($this->getOption("readmode", "lazy") == "lazy")
      $this->_readFrames();
    return $this->_cumulativeBitrate / count($this->_frames);
  }
  
  /**
   * Returns the playtime estimate, in seconds.
   *
   * @return integer
   */
  public function getLengthEstimate()
  {
    return $this->_estimatedPlayDuration;
  }
  
  /**
   * Returns the exact playtime in seconds. In lazy reading mode the frames are
   * read from the file the first time you call this method to get the exact
   * playtime of the file.
   *
   * @return integer
   */
  public function getLength()
  {
    if ($this->getOption("readmode", "lazy") == "lazy")
      $this->_readFrames();
    return $this->_cumulativePlayDuration;
  }

  /**
   * Returns the playtime estimate as a string in the form of
   * [hours]:minutes:seconds.milliseconds.
   *
   * @param integer $seconds The playtime in seconds.
   * @return string
   */
  public function getFormattedLengthEstimate()
  {
    return $this->formatTime($this->getLengthEstimate());
  }
  
  /**
   * Returns the exact playtime given in seconds as a string in the form of
   * [hours]:minutes:seconds.milliseconds. In lazy reading mode the frames are
   * read from the file the first time you call this method to get the exact
   * playtime of the file.
   *
   * @param integer $seconds The playtime in seconds.
   * @return string
   */
  public function getFormattedLength()
  {
    return $this->formatTime($this->getLength());
  }
  
  /**
   * Returns all the frames of the audio bitstream as an array. In lazy reading
   * mode the frames are read from the file the first time you call this method.
   * 
   * @return Array
   */
  public function getFrames()
  {
    if ($this->getOption("readmode", "lazy") == "lazy" &&
        $this->_frames === false) {
      $this->_readFrames();
    }
    return $this->_frames;
  }
  
  /**
   * Reads frames up to given limit. If called subsequently the method continues
   * after the last frame read in the last call, again to read up to the limit
   * or just the rest of the frames.
   * 
   * @param integer $limit The maximum number of frames read from the bitstream
   */
  private function _readFrames($limit = false)
  {
    if ($this->_lastFrameOffset !== false)
      $this->_reader->setOffset($this->_lastFrameOffset);
    
    for ($i = 0; $this->_reader->getOffset() < $this->_bytes; $i++) {
      $frame = new MPEG_Audio_Frame($this->_reader, $options);
      
      $this->_cumulativePlayDuration += 
        (double)($frame->getLength() / ($frame->getBitrate() * 1000 / 8));
      $this->_cumulativeBitrate += $frame->getBitrate();
      $this->_frames[] = $frame;
      
      if ($limit !== false && $i == $limit) {
        $this->_lastFrameOffset = $this->_reader->getOffset();
        break;
      }
    }
  }
}
