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
require_once("ISO14496/Box.php");
/**#@-*/

/**
 * A container box for all the iTunes/iPod specific boxes.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 * @since      iTunes/iPod specific
 */
final class ISO14496_Box_ILST extends ISO14496_Box
{
  private $_data = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);
    $this->setContainer(true);
    $this->constructBoxes("ISO14496_Box_ILST_Container");
  }
}

/**
 * Generic iTunes/iPod DATA Box container.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 * @since      iTunes/iPod specific
 * @ignore
 */
final class ISO14496_Box_ILST_Container extends ISO14496_Box
{
  public function __construct($reader)
  {
    parent::__construct($reader);
    $this->setContainer(true);
    $this->constructBoxes();
  }
}

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * A box that contains data for iTunes/iPod specific boxes.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 * @since      iTunes/iPod specific
 */
final class ISO14496_Box_DATA extends ISO14496_Box_Full
{
  /** @var mixed */
  private $_value;
  
  /** A flag to indicate that the data is an unsigned 8-bit integer. */
  const INTEGER = 0x0;
  
  /**
   * A flag to indicate that the data is an unsigned 8-bit integer. Different
   * value used in old versions of iTunes.
   */
  const INTEGER_OLD_STYLE = 0x15;
  
  /** A flag to indicate that the data is a string. */
  const STRING = 0x1;
  
  /** A flag to indicate that the data is the contents of an JPEG image. */
  const JPEG = 0xd;
  
  /** A flag to indicate that the data is the contents of a PNG image. */
  const PNG = 0xe;
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);
    
    $this->_reader->skip(4);
    $data = $this->_reader->read
      ($this->_offset + $this->_size - $this->_reader->getOffset());
    switch ($this->getFlags()) {
    case self::INTEGER:
    case self::INTEGER_OLD_STYLE:
      for ($i = 0;  $i < strlen($data); $i++)
        $this->_value .= ord($data[$i]);
      break;
    case self::STRING:
    default:
      $this->_value = $data;
    }
  }
  
  /**
   * Returns the value this box contains.
   * 
   * @return mixed
   */
  public function getValue() { return $this->_value; }
}
