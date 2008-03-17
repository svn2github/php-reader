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
require_once("ID3/Encoding.php");
/**#@-*/

/**
 * In the <i>General encapsulated object</i> frame any type of file can be
 * encapsulated.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_GEOB extends ID3_Frame
  implements ID3_Encoding
{
  /** @var integer */
  private $_encoding;
  
  /** @var string */
  private $_mimeType;
  
  /** @var string */
  private $_filename;
  
  /** @var string */
  private $_description;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_encoding = ord($this->_data{0});
    $this->_mimeType = substr
      ($this->_data, 1, ($pos = strpos($this->_data, "\0", 1)) - 1);
    $this->_data = substr($this->_data, $pos);
    
    switch ($this->_encoding) {
    case self::UTF16:
      $bom = substr($this->_data, 0, 2);
      $this->_data = substr($this->_data, 2);
      if ($bom == 0xfffe) {
        list ($this->_filename, $this->_description, $this->_data) =
          preg_split("/\\x00\\x00/", $this->_data, 3);
        $this->_filename = Transform::getString16LE($this->_filename);
        $this->_description = Transform::getString16LE($this->_description);
        break;
      }
    case self::UTF16BE:
      list ($this->_filename, $this->_description, $this->_data) =
        preg_split("/\\x00\\x00/", $this->_data, 3);
      $this->_filename = Transform::getString16BE($this->_filename);
      $this->_description = Transform::getString16BE($this->_description);
      break;
    default:
      list ($this->_filename, $this->_description, $this->_data) =
        preg_split("/\\x00/", $this->_data, 3);
      $this->_filename = Transform::getString8($this->_filename);
      $this->_description = Transform::getString8($this->_description);
    }
  }
  
  /**
   * Returns the text encoding.
   * 
   * @return integer
   */
  public function getEncoding() { return $this->_encoding; }

  /**
   * Returns the MIME type. The MIME type is always encoded with ISO-8859-1.
   * 
   * @return string
   */
  public function getMimeType() { return $this->_mimeType; }

  /**
   * Returns the file name.
   * 
   * @return string
   */
  public function getFilename() { return $this->_filename; }

  /**
   * Returns the file description.
   * 
   * @return string
   */
  public function getDescription() { return $this->_description; }

  /**
   * Returns the embedded object binary data.
   * 
   * @return string
   */
  public function getData() { return $this->_data; }
}
