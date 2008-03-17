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
require_once("ID3/Timing.php");
/**#@-*/

/**
 * The <i>Position synchronisation frame</i> delivers information to the
 * listener of how far into the audio stream he picked up; in effect, it states
 * the time offset from the first frame in the stream. There may only be one
 * POSS frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_POSS extends ID3_Frame
  implements ID3_Timing
{
  /** @var integer */
  private $_format;
  
  /** @var string */
  private $_position;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_format = ord($this->_data{0});
    $this->_position = Transform::getInt32BE(substr($this->_data, 1, 4));
  }

  /**
   * Returns the timing format.
   * 
   * @return integer
   */
  public function getFormat() { return $this->_format; }

  /**
   * Returns the position where in the audio the listener starts to receive,
   * i.e. the beginning of the next frame.
   * 
   * @return integer
   */
  public function getPosition() { return $this->_position; }
}
