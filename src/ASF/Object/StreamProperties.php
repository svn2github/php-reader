<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008-2009 The PHP Reader Project Workgroup. All rights
 * reserved.
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
 * @subpackage ASF
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Stream Properties Object</i> defines the specific properties and
 * characteristics of a digital media stream. This object defines how a digital
 * media stream within the <i>Data Object</i> is interpreted, as well as the
 * specific format (of elements) of the <i>Data Packet</i> itself.
 * 
 * Whereas every stream in an ASF presentation, including each stream in a
 * mutual exclusion relationship, must be represented by a <i>Stream Properties
 * Object</i>, in certain cases, this object might be found embedded in the
 * <i>Extended Stream Properties Object</i>.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_StreamProperties extends ASF_Object
{
  /**
   * Indicates, if set, that the data contained in this stream is encrypted and
   * will be unreadable unless there is a way to decrypt the stream.
   */
  const ENCRYPTED_CONTENT = 0x8000;
  
  const AUDIO_MEDIA = "f8699e40-5b4d-11cf-a8fd-00805f5c442b";
  const VIDEO_MEDIA = "bc19efc0-5b4d-11cf-a8fd-00805f5c442b";
  const COMMAND_MEDIA = "59dacfc0-59e6-11d0-a3ac-00a0c90348f6";
  const JFIF_MEDIA = "b61be100-5b4e-11cf-a8fD-00805f5c442b";
  const DEGRADABLE_JPEG_MEDIA = "35907dE0-e415-11cf-a917-00805f5c442b";
  const FILE_TRANSFER_MEDIA = "91bd222c-f21c-497a-8b6d-5aa86bfc0185";
  const BINARY_MEDIA = "3afb65e2-47ef-40f2-ac2c-70a90d71d343";
  
  const NO_ERROR_CORRECTION = "20fb5700-5b55-11cf-a8fd-00805f5c442b";
  const AUDIO_SPREAD = "bfc3cd50-618f-11cf-8bb2-00aa00b4e220";
  
  /** @var string */
  private $_streamType;

  /** @var string */
  private $_errorCorrectionType;

  /** @var integer */
  private $_timeOffset;

  /** @var integer */
  private $_flags;

  /** @var integer */
  private $_reserved;

  /** @var Array */
  private $_typeSpecificData = array();

  /** @var Array */
  private $_errorCorrectionData = array();
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_streamType = $this->_reader->readGUID();
    $this->_errorCorrectionType = $this->_reader->readGUID();
    $this->_timeOffset = $this->_reader->readInt64LE();
    $typeSpecificDataLength = $this->_reader->readUInt32LE();
    $errorCorrectionDataLength = $this->_reader->readUInt32LE();
    $this->_flags = $this->_reader->readUInt16LE();
    $this->_reserved = $this->_reader->readUInt32LE();
    
    switch ($this->_streamType) {
    case self::AUDIO_MEDIA:
      $this->_typeSpecificData = array
        ("codecId" => $this->_reader->readUInt16LE(),
         "numberOfChannels" => $this->_reader->readUInt16LE(),
         "samplesPerSecond" => $this->_reader->readUInt32LE(),
         "avgNumBytesPerSecond" => $this->_reader->readUInt32LE(),
         "blockAlignment" => $this->_reader->readUInt16LE(),
         "bitsPerSample" => $this->_reader->readUInt16LE());
      $codecSpecificDataSize = $this->_reader->readUInt16LE();
      $this->_typeSpecificData["codecSpecificData"] =
        $this->_reader->read($codecSpecificDataSize);
      break;
    case self::VIDEO_MEDIA:
      $this->_typeSpecificData = array
        ("encodedImageWidth" => $this->_reader->readUInt32LE(),
         "encodedImageHeight" => $this->_reader->readUInt32LE(),
         "reservedFlags" => $this->_reader->readInt8());
      $this->_reader->skip(2);
      $formatDataSize = $this->_reader->readUInt32LE();
      $this->_typeSpecificData = array_merge
        ($this->_typeSpecificData, array
         ("imageWidth" => $this->_reader->readUInt32LE(),
          "imageHeight" => $this->_reader->readUInt32LE(),
          "reserved" => $this->_reader->readUInt16LE(),
          "bitsPerPixelCount" => $this->_reader->readUInt16LE(),
          "compressionId" => $this->_reader->readUInt32LE(),
          "imageSize" => $this->_reader->readUInt32LE(),
          "horizontalPixelsPerMeter" => $this->_reader->readUInt32LE(),
          "verticalPixelsPerMeter" => $this->_reader->readUInt32LE(),
          "colorsUsedCount" => $this->_reader->readUInt32LE(),
          "importantColorsCount" => $this->_reader->readUInt32LE(),
          "codecSpecificData" => $this->_reader->read($formatDataSize - 38)));
      break;
    case self::JFIF_MEDIA:
      $this->_typeSpecificData = array
        ("imageWidth" => $this->_reader->readUInt32LE(),
         "imageHeight" => $this->_reader->readUInt32LE(),
         "reserved" => $this->_reader->readUInt32LE());
      break;
    case self::DEGRADABLE_JPEG_MEDIA:
      $this->_typeSpecificData = array
        ("imageWidth" => $this->_reader->readUInt32LE(),
         "imageHeight" => $this->_reader->readUInt32LE(),
         $this->_reader->readUInt16LE(),
         $this->_reader->readUInt16LE(),
         $this->_reader->readUInt16LE());
      $interchangeDataSize = $this->_reader->readUInt16LE();
      if ($interchangeDataSize == 0)
        $interchangeDataSize++;
      $this->_typeSpecificData["interchangeData"] =
        $this->_reader->read($interchangeDataSize);
      break;
    case self::FILE_TRANSFER_MEDIA:
    case self::BINARY_MEDIA:
      $this->_typeSpecificData = array
        ("majorMediaType" => $this->_reader->getGUID(),
         "mediaSubtype" => $this->_reader->getGUID(),
         "fixedSizeSamples" => $this->_reader->readUInt32LE(),
         "temporalCompression" => $this->_reader->readUInt32LE(),
         "sampleSize" => $this->_reader->readUInt32LE(),
         "formatType" => $this->_reader->getGUID());
      $formatDataSize = $this->_reader->readUInt32LE();
      $this->_typeSpecificData["formatData"] =
        $this->_reader->read($formatDataSize);
      break;
    case self::COMMAND_MEDIA:
    default:
      $this->_reader->skip($typeSpecificDataLength);
    }
    switch ($this->_errorCorrectionType) {
    case self::AUDIO_SPREAD:
      $this->_errorCorrectionData = array
        ("span" => $this->_reader->readInt8(),
         "virtualPacketLength" => $this->_reader->readUInt16LE(),
         "virtualChunkLength" => $this->_reader->readUInt16LE());
      $silenceDataSize = $this->_reader->readUInt16LE();
      $this->_errorCorrectionData["silenceData"] =
        $this->_reader->read($silenceDataSize);
      break;
    case self::NO_ERROR_CORRECTION:
    default:
      $this->_reader->skip($errorCorrectionDataLength);
    }
  }

  /**
   * Returns the number of this stream. 0 is an invalid stream. Valid values are
   * between 1 and 127. The numbers assigned to streams in an ASF presentation
   * may be any combination of unique values; parsing logic must not assume that
   * streams are numbered sequentially.
   *
   * @return integer
   */
  public function getStreamNumber() { return $this->_flags & 0x3f; }

  /**
   * Returns the number of this stream. 0 is an invalid stream. Valid values are
   * between 1 and 127. The numbers assigned to streams in an ASF presentation
   * may be any combination of unique values; parsing logic must not assume that
   * streams are numbered sequentially.
   * 
   * @param integer $streamNumber The number of this stream.
   */
  public function setStreamNumber($streamNumber)
  {
    if ($streamNumber < 1 || $streamNumber > 127) {
      require_once("ASF/Exception.php");
      throw new ASF_Exception("Invalid argument");
    }
    $this->_flags = ($this->_flags & 0xffc0) | ($streamNumber & 0x3f);
  }

  /**
   * Returns the type of the stream (for example, audio, video, and so on).
   *
   * @return string
   */
  public function getStreamType() { return $this->_streamType; }

  /**
   * Sets the type of the stream (for example, audio, video, and so on).
   * 
   * @param integer $streamType The type of the stream.
   */
  public function setStreamType($streamType)
  {
    $this->_streamType = $streamType;
  }

  /**
   * Returns the error correction type used by this digital media stream. For
   * streams other than audio, this value should be set to NO_ERROR_CORRECTION.
   * For audio streams, this value should be set to AUDIO_SPREAD.
   *
   * @return string
   */
  public function getErrorCorrectionType()
  {
    return $this->_errorCorrectionType;
  }

  /**
   * Sets the error correction type used by this digital media stream. For
   * streams other than audio, this value should be set to NO_ERROR_CORRECTION.
   * For audio streams, this value should be set to AUDIO_SPREAD.
   *
   * @param integer $errorCorrectionType The error correction type used by this
   *        digital media stream.
   */
  public function setErrorCorrectionType($errorCorrectionType)
  {
    $this->_errorCorrectionType = $errorCorrectionType;
  }

  /**
   * Returns the presentation time offset of the stream in 100-nanosecond units.
   * The value of this field is added to all of the timestamps of the samples in
   * the stream. This value shall be equal to the send time of the first
   * interleaved packet in the data section. The value of this field is
   * typically 0. It is non-zero in the case when an ASF file is edited and it
   * is not possible for the editor to change the presentation times and send
   * times of ASF packets. Note that if more than one stream is present in an
   * ASF file the offset values of all stream properties objects must be equal.
   *
   * @return integer
   */
  public function getTimeOffset() { return $this->_timeOffset; }
  
  /**
   * Sets the presentation time offset of the stream in 100-nanosecond units.
   * The value of this field is added to all of the timestamps of the samples in
   * the stream. This value shall be equal to the send time of the first
   * interleaved packet in the data section. The value of this field is
   * typically 0. It is non-zero in the case when an ASF file is edited and it
   * is not possible for the editor to change the presentation times and send
   * times of ASF packets. Note that if more than one stream is present in an
   * ASF file the offset values of all stream properties objects must be equal.
   *
   * @param integer $timeOffset The presentation time offset of the stream.
   */
  public function setTimeOffset($timeOffset)
  {
    $this->_timeOffset = $timeOffset;
  }
  
  /**
   * Checks whether or not the flag is set. Returns <var>true</var> if the flag
   * is set, <var>false</var> otherwise.
   * 
   * @param integer $flag The flag to query.
   * @return boolean
   */
  public function hasFlag($flag) { return ($this->_flags & $flag) == $flag; }
  
  /**
   * Returns the flags field.
   *
   * @return integer
   */
  public function getFlags() { return $this->_flags; }
  
  /**
   * Sets the flags field.
   *
   * @param integer $flags The flags field.
   */
  public function setFlags($flags) { $this->_flags = $flags; }
  
  /**
   * Returns type-specific format data. The structure for the <i>Type-Specific
   * Data</i> field is determined by the value stored in the <i>Stream Type</i>
   * field.
   * 
   * The type-specific data is returned as key-value pairs of an associate
   * array.
   *
   * @return Array
   */
  public function getTypeSpecificData() { return $this->_typeSpecificData; }
  
  /**
   * Sets type-specific format data. The structure for the <i>Type-Specific
   * Data</i> field is determined by the value stored in the <i>Stream Type</i>
   * field.
   * 
   * @param Array $typeSpecificData The type-specific data as key-value pairs of
   *        an associate array.
   */
  public function setTypeSpecificData($typeSpecificData)
  {
    $this->_typeSpecificData = $typeSpecificData;
  }

  /**
   * Returns data specific to the error correction type. The structure for the
   * <i>Error Correction Data</i> field is determined by the value stored in the
   * <i>Error Correction Type</i> field. For example, an audio data stream might
   * need to know how codec chunks were redistributed, or it might need a sample
   * of encoded silence.
   * 
   * The error correction type-specific data is returned as key-value pairs of
   * an associate array.
   *
   * @return integer
   */
  public function getErrorCorrectionData()
  {
    return $this->_errorCorrectionData;
  }
  
  /**
   * Sets data specific to the error correction type. The structure for the
   * <i>Error Correction Data</i> field is determined by the value stored in the
   * <i>Error Correction Type</i> field. For example, an audio data stream might
   * need to know how codec chunks were redistributed, or it might need a sample
   * of encoded silence.
   * 
   * @param Array $errorCorrectionData The error correction type-specific data
   *        as key-value pairs of an associate array.
   */
  public function setErrorCorrectionData($errorCorrectionData)
  {
    $this->_errorCorrectionData = $errorCorrectionData;
  }
  
  /**
   * Returns the whether the object is required to be present, or whether
   * minimum cardinality is 1.
   * 
   * @return boolean
   */
  public function isMandatory() { return true; }
  
  /**
   * Returns whether multiple instances of this object can be present, or
   * whether maximum cardinality is greater than 1.
   * 
   * @return boolean
   */
  public function isMultiple() { return false; }
  
  /**
   * Returns the object data with headers.
   *
   * @return string
   */
  public function __toString()
  {
    $data = 
      Transform::toGUID($this->_streamType) .
      Transform::toGUID($this->_errorCorrectionType) .
      Transform::toInt64LE($this->_timeOffset);
    
    switch ($this->_streamType) {
    case self::AUDIO_MEDIA:
      $typeSpecificData = 
        Transform::toUInt16LE($this->_typeSpecificData["codecId"]) .
        Transform::toUInt16LE($this->_typeSpecificData["numberOfChannels"]) .
        Transform::toUInt32LE($this->_typeSpecificData["samplesPerSecond"]) .
        Transform::toUInt32LE
          ($this->_typeSpecificData["avgNumBytesPerSecond"]) .
        Transform::toUInt16LE($this->_typeSpecificData["blockAlignment"]) .
        Transform::toUInt16LE($this->_typeSpecificData["bitsPerSample"]) .
        Transform::toUInt16LE
          (strlen($this->_typeSpecificData["codecSpecificData"])) .
        $this->_typeSpecificData["codecSpecificData"];
      break;
    case self::VIDEO_MEDIA:
      $typeSpecificData = 
        Transform::toUInt32LE($this->_typeSpecificData["encodedImageWidth"]) .
        Transform::toUInt32LE($this->_typeSpecificData["encodedImageHeight"]) .
        Transform::toInt8($this->_typeSpecificData["reservedFlags"]) .
        Transform::toUInt16LE(0) . // Reserved
        Transform::toUInt32LE
          (38 + strlen($this->_typeSpecificData["codecSpecificData"])) .
        Transform::toUInt32LE($this->_typeSpecificData["imageWidth"]) .
        Transform::toUInt32LE($this->_typeSpecificData["imageHeight"]) .
        Transform::toUInt16LE($this->_typeSpecificData["reserved"]) .
        Transform::toUInt16LE($this->_typeSpecificData["bitsPerPixelCount"]) .
        Transform::toUInt32LE($this->_typeSpecificData["compressionId"]) .
        Transform::toUInt32LE($this->_typeSpecificData["imageSize"]) .
        Transform::toUInt32LE
          ($this->_typeSpecificData["horizontalPixelsPerMeter"]) .
        Transform::toUInt32LE
          ($this->_typeSpecificData["verticalPixelsPerMeter"]) .
        Transform::toUInt32LE($this->_typeSpecificData["colorsUsedCount"]) .
        Transform::toUInt32LE
          ($this->_typeSpecificData["importantColorsCount"]) .
        $this->_typeSpecificData["codecSpecificData"];
      break;
    case self::JFIF_MEDIA:
      $typeSpecificData = 
        Transform::toUInt32LE($this->_typeSpecificData["imageWidth"]) .
        Transform::toUInt32LE($this->_typeSpecificData["imageHeight"]) .
        Transform::toUInt32LE(0);
      break;
    case self::DEGRADABLE_JPEG_MEDIA:
      $typeSpecificData = 
        Transform::toUInt32LE($this->_typeSpecificData["imageWidth"]) .
        Transform::toUInt32LE($this->_typeSpecificData["imageHeight"]) .
        Transform::toUInt16LE(0) .
        Transform::toUInt16LE(0) .
        Transform::toUInt16LE(0);
      $interchangeDataSize = strlen
        ($this->_typeSpecificData["interchangeData"]);
      if ($interchangeDataSize == 1)
        $interchangeDataSize = 0;
      $typeSpecificData .= 
        Transform::toUInt16LE($interchangeDataSize) .
        $this->_typeSpecificData["interchangeData"];
      break;
    case self::FILE_TRANSFER_MEDIA:
    case self::BINARY_MEDIA:
      $typeSpecificData = 
        Transform::toGUID($this->_typeSpecificData["majorMediaType"]) .
        Transform::toGUID($this->_typeSpecificData["mediaSubtype"]) .
        Transform::toUInt32LE($this->_typeSpecificData["fixedSizeSamples"]) .
        Transform::toUInt32LE($this->_typeSpecificData["temporalCompression"]) .
        Transform::toUInt32LE($this->_typeSpecificData["sampleSize"]) .
        Transform::toGUID($this->_typeSpecificData["formatType"]) .
        Transform::toUInt32LE(strlen($this->_typeSpecificData["formatData"])) .
        $this->_typeSpecificData["formatData"];
      break;
    case self::COMMAND_MEDIA:
    default:
      $typeSpecificData = "";
    }
    switch ($this->_errorCorrectionType) {
    case self::AUDIO_SPREAD:
      $errorCorrectionData = 
        Transform::toInt8($this->_errorCorrectionData["span"]) .
        Transform::toUInt16LE
          ($this->_errorCorrectionData["virtualPacketLength"]) .
        Transform::toUInt16LE
          ($this->_errorCorrectionData["virtualChunkLength"]) .
        Transform::toUInt16LE
          (strlen($this->_errorCorrectionData["silenceData"])) .
        $this->_errorCorrectionData["silenceData"];
      break;
    case self::NO_ERROR_CORRECTION:
    default:
      $errorCorrectionData = "";
    }
    
    $data .= 
      Transform::toUInt32LE(strlen($typeSpecificData)) .
      Transform::toUInt32LE(strlen($errorCorrectionData)) .
      Transform::toUInt16LE($this->_flags) .
      Transform::toUInt32LE($this->_reserved) .
      $typeSpecificData . $errorCorrectionData;
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
