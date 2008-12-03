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
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("Reader.php");
require_once("Twiddling.php");
require_once("MPEG/Object.php");
/**#@-*/

/**
 * This class represents a LAME extension to the Xing VBR header. The purpose of
 * this header is to provide extra information about the MP3 bistream, encoder
 * and parameters used. This header should, as much as possible, be meaningfull
 * for as many encoders as possible, even if it is unlikely that other encoders
 * than LAME will implement it.
 * 
 * This header should be backward compatible with the Xing VBR tag, providing
 * basic support for a lot of already written software. As much as possible the
 * current revision (revision 1) should provide information similar to the one
 * already provided by revision 0.
 *
 * @package    php-reader
 * @subpackage MPEG
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev: 1 $
 */
class MPEG_Audio_LAMEHeader extends MPEG_Object
{
  
  /** @var integer */
  const VBR_METHOD_CONSTANT = 1;
  
  /** @var integer */
  const VBR_METHOD_ABR = 2;
  
  /** @var integer */
  const VBR_METHOD_RH = 3;
  
  /** @var integer */
  const VBR_METHOD_MTRH = 4;
  
  /** @var integer */
  const VBR_METHOD_MT = 5;
  
  /** @var integer */
  const ENCODING_FLAG_NSPSYTUNE = 1;
  
  /** @var integer */
  const ENCODING_FLAG_NSSAFEJOINT = 2;
  
  /** @var integer */
  const ENCODING_FLAG_NOGAP_CONTINUED = 4;
  
  /** @var integer */
  const ENCODING_FLAG_NOGAP_CONTINUATION = 8;

  /** @var integer */
  const MODE_MONO = 0;
  
  /** @var integer */
  const MODE_STEREO = 1;
  
  /** @var integer */
  const MODE_DUAL = 2;
  
  /** @var integer */
  const MODE_JOINT = 3;
  
  /** @var integer */
  const MODE_FORCE = 4;
  
  /** @var integer */
  const MODE_AUTO = 5;
  
  /** @var integer */
  const MODE_INTENSITY = 6;
  
  /** @var integer */
  const MODE_UNDEFINED = 7;
  
  /** @var integer */
  const SOURCE_FREQUENCY_32000_OR_LOWER = 0;
  
  /** @var integer */
  const SOURCE_FREQUENCY_44100 = 1;
  
  /** @var integer */
  const SOURCE_FREQUENCY_48000 = 2;
  
  /** @var integer */
  const SOURCE_FREQUENCY_HIGHER = 3;
  
  /** @var integer */
  const SURROUND_NONE = 0;
  
  /** @var integer */
  const SURROUND_DPL = 1;
  
  /** @var integer */
  const SURROUND_DPL2 = 2;
  
  /** @var integer */
  const SURROUND_AMBISONIC = 3;
  
  /** @var string */
  private $_version;

  /** @var integer */
  private $_revision;

  /** @var integer */
  private $_vbrMethod;

  /** @var integer */
  private $_lowpass;

  /** @var integer */
  private $_peakSignalAmplitude;

  /** @var integer */
  private $_radioReplayGain;

  /** @var integer */
  private $_audiophileReplayGain;

  /** @var integer */
  private $_encodingFlags;

  /** @var integer */
  private $_athType;

  /** @var integer */
  private $_bitrate;
  
  /** @var integer */
  private $_encoderDelaySamples;
  
  /** @var integer */
  private $_paddedSamples;
  
  /** @var integer */
  private $_sourceSampleFrequency;
  
  /** @var boolean */
  private $_unwiseSettingsUsed;
  
  /** @var integer */
  private $_mode;
  
  /** @var integer */
  private $_noiseShaping;
  
  /** @var integer */
  private $_mp3Gain;
  
  /** @var integer */
  private $_surroundInfo;
  
  /** @var integer */
  private $_presetUsed;
  
  /** @var integer */
  private $_musicLength;
  
  /** @var integer */
  private $_musicCrc;
  
  /** @var integer */
  private $_crc;
  
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
    
    $this->_version = $this->_reader->readString8(5);
    
    $tmp = $this->_reader->readUInt8();
    $this->_revision = Twiddling::getValue($tmp, 4, 8);
    $this->_vbrMethod = Twiddling::getValue($tmp, 0, 3);
    
    $this->_lowpass = $this->_reader->readUInt8() * 100;

    $this->_peakSignalAmplitude = $this->_reader->readUInt32BE();
    
    $tmp = $this->_reader->readUInt16BE();
    $this->_radioReplayGain = array(
      "name" => Twiddling::getValue($tmp, 0, 2),
      "originator" => Twiddling::getValue($tmp, 3, 5),
      "absoluteGainAdjustment" => Twiddling::getValue($tmp, 7, 15) / 10
    );
    
    $tmp = $this->_reader->readUInt16BE();
    $this->_audiophileReplayGain = array(
      "name" => Twiddling::getValue($tmp, 0, 2),
      "originator" => Twiddling::getValue($tmp, 3, 5),
      "absoluteGainAdjustment" => Twiddling::getValue($tmp, 7, 15) / 10
    );
    
    $tmp = $this->_reader->readUInt8();
    $this->_encodingFlags = Twiddling::getValue($tmp, 4, 8);
    $this->_athType = Twiddling::getValue($tmp, 0, 3);
    
    $this->_bitrate = $this->_reader->readUInt8();
    
    $tmp = $this->_reader->readUInt32BE();
    // Encoder delay fields
    $this->_encoderDelaySamples = Twiddling::getValue($tmp, 20, 31);
    $this->_paddedSamples = Twiddling::getValue($tmp, 8, 19);
    // Misc field
    $this->_sourceSampleFrequency = Twiddling::getValue($tmp, 6, 7);
    $this->_unwiseSettingsUsed = Twiddling::testBit($tmp, 5);
    $this->_mode = Twiddling::getValue($tmp, 2, 4);
    $this->_noiseShaping = Twiddling::getValue($tmp, 0, 1);
    
    $this->_mp3Gain = pow(2, $this->_reader->readInt8() / 4);
    
    $tmp = $this->_reader->readUInt16BE();
    $this->_surroundInfo = Twiddling::getValue($tmp, 11, 14);
    $this->_presetUsed = Twiddling::getValue($tmp, 0, 10);
    
    $this->_musicLength = $this->_reader->readUInt32BE();
    
    $this->_musicCrc = $this->_reader->readUInt16BE();
    $this->_crc = $this->_reader->readUInt16BE();
  }
  
  /**
   * Returns the version string of the header.
   *
   * @return string
   */
  public function getVersion() { return $this->_version; }
  
  /**
   * Returns the info tag revision.
   *
   * @return integer
   */
  public function getRevision() { return $this->_revision; }
  
  /**
   * Returns the VBR method used for encoding. See the corresponding constants
   * for possible return values.
   *
   * @return integer
   */
  public function getVbrMethod() { return $this->_vbrMethod; }
  
  /**
   * Returns the lowpass filter value.
   *
   * @return integer
   */
  public function getLowpass() { return $this->_lowpass; }
  
  /**
   * Returns the peak signal amplitude field of replay gain. The value of 1.0
   * (ie 100%) represents maximal signal amplitude storeable in decoding format.
   *
   * @return integer
   */
  public function getPeakSignalAmplitude()
  {
    return $this->_peakSignalAmplitude;
  }
  
  /**
   * Returns the radio replay gain field of replay gain, required to make all
   * tracks equal loudness, as an array that consists of the following keys.
   * 
   *   o name -- Specifies the name of the gain adjustment. Can be one of the
   *     following values: 0 = not set, 1 = radio, or 2 = audiophile.
   * 
   *   o originator -- Specifies the originator of the gain adjustment. Can be
   *     one of the following values: 0 = not set, 1 = set by artist, 2 = set
   *     by user, 3 = set by my model, 4 = set by simple RMS average.
   * 
   *   o absoluteGainAdjustment -- Speficies the absolute gain adjustment.
   *
   * @return Array
   */
  public function getRadioReplayGain() { return $this->_radioReplayGain; }
  
  /**
   * Returns the audiophile replay gain field of replay gain, required to give
   * ideal listening loudness, as an array that consists of the following keys.
   * 
   *   o name -- Specifies the name of the gain adjustment. Can be one of the
   *     following values: 0 = not set, 1 = radio, or 2 = audiophile.
   * 
   *   o originator -- Specifies the originator of the gain adjustment. Can be
   *     one of the following values: 0 = not set, 1 = set by artist, 2 = set
   *     by user, 3 = set by my model, 4 = set by simple RMS average.
   * 
   *   o absoluteGainAdjustment -- Speficies the absolute gain adjustment.
   *
   * @return Array
   */
  public function getAudiophileReplayGain()
  {
    return $this->_audiophileReplayGain;
  }
  
  /**
   * Returns the encoding flags. See the corresponding flag constants for
   * possible values.
   *
   * @return integer
   */
  public function getEncodingFlags() { return $this->_encodingFlags; }
  
  /**
   * Returns the ATH type.
   *
   * @return integer
   */
  public function getAthType() { return $this->_athType; }
  
  /**
   * Returns the bitrate for CBR encoded files and the minimal birate for
   * VBR encoded file. The maximum value of this field is 255 even with higher
   * actual bitrates.
   *
   * @return integer
   */
  public function getBitrate() { return $this->_bitrate; }
  
  /**
   * Returns the encoder delay or number of samples added at start.
   *
   * @return integer
   */
  public function getEncoderDelaySamples()
  {
    return $this->_encoderDelaySamples;
  }
  
  /**
   * Returns the number of padded samples to complete the last frame.
   *
   * @return integer
   */
  public function getPaddedSamples() { return $this->_paddedSamples; }
  
  /**
   * Returns the source sample frequency. See corresponding constants for
   * possible values.
   *
   * @return integer
   */
  public function getSourceSampleFrequency()
  {
    return $this->_sourceSampleFrequency;
  }
  
  /**
   * An alias to getUnwiseSettingsUsed().
   *
   * @see getUnwiseSettingsUsed
   * @return boolean
   */
  public function areUnwiseSettingsUsed()
  {
    return $this->getUnwiseSettingsUsed();
  }
  
  /**
   * Returns whether unwise settings were used to encode the file.
   *
   * @return boolean
   */
  public function getUnwiseSettingsUsed() { return $this->_unwiseSettingsUsed; }
  
  /**
   * Returns the stereo mode. See corresponding constants for possible values.
   *
   * @return integer
   */
  public function getMode() { return $this->_mode; }
  
  /**
   * Returns the noise shaping.
   *
   * @return integer
   */
  public function getNoiseShaping() { return $this->_noiseShaping; }
  
  /**
   * Returns the MP3 gain change. Any MP3 can be amplified in a lossless manner.
   * If done so, this field can be used to log such transformation happened so
   * that any given time it can be undone.
   *
   * @return integer
   */
  public function getMp3Gain() { return $this->_mp3Gain; }
  
  /**
   * Returns the surround info. See corresponding contants for possible values.
   *
   * @return integer
   */
  public function getSurroundInfo() { return $this->_surroundInfo; }
  
  /**
   * Returns the preset used in encoding.
   *
   * @return integer
   */
  public function getPresetUsed() { return $this->_presetUsed; }
  
  /**
   * Returns the exact length in bytes of the MP3 file originally made by LAME
   * excluded ID3 tag info at the end.
   * 
   * The first byte it counts is the first byte of this LAME header and the last
   * byte it counts is the last byte of the last MP3 frame containing music.
   * The value should be equal to file length at the time of LAME encoding,
   * except when using ID3 tags.
   *
   * @return integer
   */
  public function getMusicLength() { return $this->_musicLength; }
  
  /**
   * Returns the CRC-16 of the complete MP3 music data as made originally by
   * LAME.
   *
   * @return integer
   */
  public function getMusicCrc() { return $this->_musicCrc; }
  
  /**
   * Returns the CRC-16 of the first 190 bytes of the header frame.
   *
   * @return integer
   */
  public function getCrc() { return $this->_crc; }
}
