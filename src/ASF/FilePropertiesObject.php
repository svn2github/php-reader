<?php
/**
 * $Id$
 *
 *
 * Copyright (C) 2006, 2007 The Bearpaw Project Work Group. All Rights Reserved.
 * Copyright (C) 2007, 2008 BEHR Software Systems. All Rights Reserved.
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
 * @package   php-reader
 */

/**#@+ @ignore */
require_once("Object.php");
/**#@-*/

/**
 * The ASF_File_Properties_Object object implementation. This object contains
 * various information about the ASF file.
 *
 * @package   php-reader
 * @author    Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright 2006, 2007 The Bearpaw Project Work Group
 * @copyright 2007, 2008 BEHR Software Systems
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   $Rev$
 */
final class ASF_FilePropertiesObject extends ASF_Object
{
  /**
   * @var string The file id field.
   */
  private $_fileId;

  /**
   * @var string The file size field.
   */
  private $_fileSize;

  /**
   * @var string The creation date field.
   */
  private $_creationDate;

  /**
   * @var string The data packets count field.
   */
  private $_dataPacketsCount;

  /**
   * @var string The play duration field.
   */
  private $_playDuration;

  /**
   * @var string The send duration field.
   */
  private $_sendDuration;

  /**
   * @var string The preroll field.
   */
  private $_preroll;

  /**
   * @var string The flags field.
   */
  private $_flags;

  /**
   * @var string The minimum data packet size field.
   */
  private $_minimumDataPacketSize;

  /**
   * @var string The maximum data packet size field.
   */
  private $_maximumDataPacketSize;

  /**
   * @var string The maximum bitrate field.
   */
  private $_maximumBitrate;
  
  /**
   * Initiates the object class with given parameters and reads and processes
   * the object information from the ASF file. 
   */
  public function __construct($reader, $id, $size)
  {
    parent::__construct($reader, $id, $size);
    
    $this->_fileId = $this->_reader->getGUID();
    $this->_fileSize = $this->_reader->getInt64LE();
    $this->_creationDate = $this->_reader->getInt64LE();
    $this->_dataPacketsCount = $this->_reader->getInt64LE();

    $seconds = floor($this->_reader->getInt64LE() / 10000000);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $this->_playDuration =
      ($minutes > 0 ?
       ($hours > 0 ? $hours . ":" .
        str_pad($minutes % 60, 2, "0", STR_PAD_LEFT) : $minutes % 60) . ":" .
        str_pad($seconds % 60, 2, "0", STR_PAD_LEFT) : $seconds % 60);

    $seconds = floor($this->_reader->getInt64LE() / 10000000);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $this->_sendDuration =
      ($minutes > 0 ?
       ($hours > 0 ? $hours . ":" .
        str_pad($minutes % 60, 2, "0", STR_PAD_LEFT) : $minutes % 60) . ":" .
        str_pad($seconds % 60, 2, "0", STR_PAD_LEFT) : $seconds % 60);

    $this->_preroll = $this->_reader->getInt64LE();
    $this->_flags = $this->_reader->getUInt32LE();
    $this->_minimumDataPacketSize = $this->_reader->getUInt32LE();
    $this->_maximumDataPacketSize = $this->_reader->getUInt32LE();
    $this->_maximumBitrate = $this->_reader->getUInt32LE();
  }

  /**
   * Returns the file id field.
   *
   * @return integer Returns the file id field.
   */
  public function getFileId() { return $this->_fileId; }

  /**
   * Returns the file size field.
   *
   * @return integer Returns the file size field.
   */
  public function getFileSize() { return $this->_fileSize; }

  /**
   * Returns the creation date field.
   *
   * @return integer Returns the creation date field.
   */
  public function getCreationDate() { return $this->_creationDate; }

  /**
   * Returns the data packets field.
   *
   * @return integer Returns the data packets field.
   */
  public function getDataPacketsCount() { return $this->_dataPacketsCount; }

  /**
   * Returns the play duration field.
   *
   * @return integer Returns the play duration field.
   */
  public function getPlayDuration() { return $this->_playDuration; }

  /**
   * Returns the send duration field.
   *
   * @return integer Returns the send duration field.
   */
  public function getSendDuration() { return $this->_sendDuration; }

  /**
   * Returns the preroll field.
   *
   * @return integer Returns the preroll field.
   */
  public function getPreroll() { return $this->_preroll; }

  /**
   * Returns the flags field.
   *
   * @return integer Returns the flags field.
   */
  public function getFlags() { return $this->_flags; }
  
  /**
   * Returns the minimum data packet size field.
   * 
   * @return integer Returns the minimum data packet size field.
   */
  public function getMinimumDataPacketSize()
  {
    return $this->_minimumDataPacketSize;
  }
  
  /**
   * Returns the maximum data packet size field.
   * 
   * @return integer Returns the maximum data packet size field.
   */
  public function getMaximumDataPacketSize()
  {
    return $this->_maximumDataPacketSize;
  }
  
  /**
   * Returns the maximum bitrate field.
   * 
   * @return integer Returns the maximum bitrate field.
   */
  public function getMaximumBitrate()
  {
    return $this->_maximumBitrate;
  }
}
