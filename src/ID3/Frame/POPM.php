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
 * The purpose of the <i>Popularimeter</i> frame is to specify how good an audio
 * file is. Many interesting applications could be found to this frame such as a
 * playlist that features better audio files more often than others or it could
 * be used to profile a person's taste and find other good files by comparing
 * people's profiles. The frame contains the email address to the user, one
 * rating byte and a four byte play counter, intended to be increased with one
 * for every time the file is played.
 *
 * The rating is 1-255 where 1 is worst and 255 is best. 0 is unknown. If no
 * personal counter is wanted it may be omitted. When the counter reaches all
 * one's, one byte is inserted in front of the counter thus making the counter
 * eight bits bigger in the same away as the play counter
 * {@link ID3_Frame_PCNT}. There may be more than one POPM frame in each tag,
 * but only one with the same email address.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_POPM extends ID3_Frame
{
  /** @var string */
  private $_id;
  
  /** @var integer */
  private $_rating;
  
  /** @var integer */
  private $_counter;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    list($this->_id, $this->_data) = preg_split("/\\x00/", $this->_data, 2);
    $this->_rating = substr($this->_data, 0, 1);
    $this->_data = substr($this->_data, 1);
    
    switch (strlen($this->_data)) {
    case 8:
      $this->_counter = Transform::fromInt64BE($this->_data);
      break;
    case 4:
      $this->_counter = Transform::fromInt32BE($this->_data);
      break;
    }
  }

  /**
   * Returns the user identifier string.
   * 
   * @return string
   */
  public function getIdentifier() { return $this->_id; }


  /**
   * Returns the user rating.
   * 
   * @return integer
   */
  public function getRating() { return $this->_rating; }
  
  /**
   * Returns the counter.
   * 
   * @return integer
   */
  public function getCounter() { return $this->_counter; }
}
