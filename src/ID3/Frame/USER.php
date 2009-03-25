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
 * @subpackage ID3
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
require_once("ID3/Encoding.php");
require_once("ID3/Language.php");
/**#@-*/

/**
 * The <i>Terms of use frame</i> contains a brief description of the terms of
 * use and ownership of the file. More detailed information concerning the legal
 * terms might be available through the {@link ID3_Frame_WCOP} frame. Newlines
 * are allowed in the text. There may be more than one Terms of use frames in a
 * tag, but only one with the same language.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_USER extends ID3_Frame
  implements ID3_Encoding, ID3_Language
{
  /** @var integer */
  private $_encoding;
  
  /** @var string */
  private $_language = "und";

  /** @var string */
  private $_text;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $this->_encoding = $this->getOption("encoding", ID3_Encoding::UTF8);
    
    if ($reader === null)
      return;
    
    $encoding = Transform::fromUInt8($this->_data[0]);
    $this->_language = substr($this->_data, 1, 3);
    if ($this->_language == "XXX")
      $this->_language = "und";
    $this->_data = substr($this->_data, 4);

    switch ($encoding) {
    case self::UTF16:
      $this->_text = $this->_convertString
        (Transform::fromString16($this->_data), "utf-16");
      break;
    case self::UTF16BE:
      $this->_text = $this->_convertString
        (Transform::fromString16($this->_data), "utf-16be");
      break;
    case self::UTF8:
      $this->_text = $this->_convertString
        (Transform::fromString8($this->_data), "utf-8");
      break;
    default:
      $this->_text = $this->_convertString
        (Transform::fromString8($this->_data), "iso-8859-1");
    }
  }
  
  /**
   * Returns the text encoding.
   * 
   * All the strings read from a file are automatically converted to the
   * character encoding specified with the <var>encoding</var> option. See
   * {@link ID3v2} for details. This method returns the original text encoding
   * used to write the frame.
   * 
   * @return integer
   */
  public function getEncoding() { return $this->_encoding; }
  
  /**
   * Sets the text encoding.
   * 
   * All the string written to the frame are done so using given character
   * encoding. No conversions of existing data take place upon the call to this
   * method thus all texts must be given in given character encoding.
   * 
   * The default character encoding used to write the frame is UTF-8.
   * 
   * @see ID3_Encoding
   * @param integer $encoding The text encoding.
   */
  public function setEncoding($encoding) { $this->_encoding = $encoding; }
  
  /**
   * Returns the language code as specified in the
   * {@link http://www.loc.gov/standards/iso639-2/ ISO-639-2} standard.
   * 
   * @return string
   */
  public function getLanguage() { return $this->_language; }
  
  /**
   * Sets the text language code as specified in the
   * {@link http://www.loc.gov/standards/iso639-2/ ISO-639-2} standard.
   * 
   * @see ID3_Language
   * @param string $language The language code.
   */
  public function setLanguage($language)
  {
    if ($language == "XXX")
      $language = "und";
    $this->_language = substr($language, 0, 3);
  }
  
  /**
   * Returns the text.
   * 
   * @return string
   */
  public function getText() { return $this->_text; }
  
  /**
   * Sets the text using given language and encoding.
   * 
   * @param string $text The text.
   * @param string $language The language code.
   * @param integer $encoding The text encoding.
   */
  public function setText($text, $language = false, $encoding = false)
  {
    $this->_text = $text;
    if ($language !== false)
      $this->setLanguage($language);
    if ($encoding !== false)
      $this->setEncoding($encoding);
  }
  
  /**
   * Returns the frame raw data without the header.
   *
   * @return string
   */
  protected function _getData()
  {
    $data = Transform::toUInt8($this->_encoding) . $this->_language;
    switch ($this->_encoding) {
    case self::UTF16LE:
      $data .= Transform::toString16
        ($this->_text, Transform::MACHINE_ENDIAN_ORDER);
      break;
    case self::UTF16:
    case self::UTF16BE:
      $data .= Transform::toString16($this->_text);
      break;
    default:
      $data .= $this->_text;
    }
    return $data;
  }
}
