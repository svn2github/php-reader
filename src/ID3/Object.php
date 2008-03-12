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

/**
 * The base class for all ID3v2 objects.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev$
 */
abstract class ID3_Object
{
  /**
   * The reader object.
   *
   * @var Reader
   */
  protected $_reader;
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ID3v2 tag.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    $this->_reader = $reader;
  }
  
  /**
   * Encodes the given 32-bit integer to 28-bit synchsafe integer, where the
   * most significant bit of each byte is zero, making seven bits out of eight
   * available.
   * 
   * @param integer $val The integer to encode.
   * @return integer
   */
  protected function encodeSynchsafe32($val) {
    for ($i = 0, $mask = 0xffffff00; $i < 4; $i++, $mask <<= 8)
      $val = ($val << 1 & $mask) | ($val << 1 & ~$mask) >> 1;
    return $val & 0x7fffffff;
  }

  /**
   * Decodes the given 28-bit synchsafe integer to regular 32-bit integer.
   * 
   * @param integer $val The integer to decode
   * @return integer
   */
  protected function decodeSynchsafe32($val) {
    for ($i = 0, $mask = 0xff000000; $i < 3; $i++, $mask >>= 8)
      $val = ($val & $mask) >> 1 | ($val & ~$mask);
    return $val;
  }
}
