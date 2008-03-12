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
 * The <i>Reverb</i> is yet another subjective frame, with which you can adjust
 * echoes of different kinds. Reverb left/right is the delay between every
 * bounce in milliseconds. Reverb bounces left/right is the number of bounces
 * that should be made. $FF equals an infinite number of bounces. Feedback is
 * the amount of volume that should be returned to the next echo bounce. $00 is
 * 0%, $FF is 100%. If this value were $7F, there would be 50% volume reduction
 * on the first bounce, 50% of that on the second and so on. Left to left means
 * the sound from the left bounce to be played in the left speaker, while left
 * to right means sound from the left bounce to be played in the right speaker.
 *
 * Premix left to right is the amount of left sound to be mixed in the right
 * before any reverb is applied, where $00 id 0% and $FF is 100%. Premix right
 * to left does the same thing, but right to left. Setting both premix to $FF
 * would result in a mono output (if the reverb is applied symmetric). There may
 * only be one RVRB frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_RVRB extends ID3_Frame
{
  /** @var integer */
  private $_reverbLeft;
  
  /** @var integer */
  private $_reverbRight;
  
  /** @var integer */
  private $_reverbBouncesLeft;
  
  /** @var integer */
  private $_reverbBouncesRight;
  
  /** @var integer */
  private $_reverbFeedbackLtoL;
  
  /** @var integer */
  private $_reverbFeedbackLtoR;

  /** @var integer */
  private $_reverbFeedbackRtoR;

  /** @var integer */
  private $_reverbFeedbackRtoL;
  
  /** @var integer */
  private $_premixLtoR;
  
  /** @var integer */
  private $_premixRtoL;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_reverbLeft = substr($this->_data, 0, 2);
    $this->_reverbRight = substr($this->_data, 2, 2);
    $this->_reverbBouncesLeft = substr($this->_data, 4, 1);
    $this->_reverbBouncesRight = substr($this->_data, 5, 1);
    $this->_reverbFeedbackLtoL = substr($this->_data, 6, 1);
    $this->_reverbFeedbackLtoR = substr($this->_data, 7, 1);
    $this->_reverbFeedbackRtoR = substr($this->_data, 8, 1);
    $this->_reverbFeedbackRtoL = substr($this->_data, 9, 1);
    $this->_premixLtoR = substr($this->_data, 10, 1);
    $this->_premixRtoL = substr($this->_data, 11, 1);
  }
  
  /**
   * Returns the left reverb.
   * 
   * @return integer
   */
  public function getReverbLeft() { return $this->_reverbLeft; }

  /**
   * Returns the right reverb.
   * 
   * @return integer
   */
  public function getReverbRight() { return $this->_reverbRight; }

  /**
   * Returns the left reverb bounces.
   * 
   * @return integer
   */
  public function getReverbBouncesLeft() { return $this->_reverbBouncesLeft; }

  /**
   * Returns the right reverb bounces.
   * 
   * @return integer
   */
  public function getReverbBouncesRight() { return $this->_reverbBouncesRight; }

  /**
   * Returns the left-to-left reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackLtoL()
  {
    return $this->_reverbFeedbackLtoL;
  }

  /**
   * Returns the left-to-right reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackLtoR()
  {
    return $this->_reverbFeedbackLtoR;
  }

  /**
   * Returns the right-to-right reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackRtoR()
  {
    return $this->_reverbFeedbackRtoR;
  }

  /**
   * Returns the right-to-left reverb feedback.
   * 
   * @return integer
   */
  public function getReverbFeedbackRtoL()
  {
    return $this->_reverbFeedbackRtoL;
  }

  /**
   * Returns the left-to-right premix.
   * 
   * @return integer
   */
  public function getPremixLtoR()
  {
    return $this->_premixLtoR;
  }

  /**
   * Returns the right-to-left premix.
   * 
   * @return integer
   */
  public function getPremixRtoL()
  {
    return $this->_premixRtoL;
  }
}
