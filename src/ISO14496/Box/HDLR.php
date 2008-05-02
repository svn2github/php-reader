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
 * @subpackage ISO 14496
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Handler Reference Box</i> is within a {@link ISO14496_Box_MDIA Media
 * Box} declares the process by which the media-data in the track is presented,
 * and thus, the nature of the media in a track. For example, a video track
 * would be handled by a video handler.
 *
 * This box when present within a {@link ISO14496_Box_META Meta Box}, declares
 * the structure or format of the <i>meta</i> box contents.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ISO14496_Box_HDLR extends ISO14496_Box_Full
{
  /** @var string */
  private $_handlerType;

  /** @var string */
  private $_name;

  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader  $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);
    
    $this->_reader->skip(4);
    $this->_handlerType = $this->_reader->read(4);
    $this->_reader->skip(12);
    $this->_name = $this->_reader->readString8
      ($this->_offset + $this->_size - $this->_reader->getOffset());
  }
  
  /**
   * Returns the handler type.
   * 
   * When present in a media box, the returned value contains one of the
   * following values, or a value from a derived specification:
   *   o <i>vide</i> Video track
   *   o <i>soun</i> Audio track
   *   o <i>hint</i> Hint track
   * 
   * When present in a meta box, the returned value contains an appropriate
   * value to indicate the format of the meta box contents.
   * 
   * @return integer
   */
  public function getHandlerType() { return $this->_handlerType; }
  
  /**
   * Returns the name string. The name is in UTF-8 characters and gives a
   * human-readable name for the track type (for debugging and inspection
   * purposes).
   * 
   * @return integer
   */
  public function getName() { return $this->_name; }
}
