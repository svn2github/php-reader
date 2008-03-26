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
 * To identify with which method a frame has been encrypted the encryption
 * method must be registered in the tag with the <i>Encryption method
 * registration</i> frame.
 *
 * The owner identifier a URL containing an email address, or a link to a
 * location where an email address can be found, that belongs to the
 * organisation responsible for this specific encryption method. Questions
 * regarding the encryption method should be sent to the indicated email
 * address.
 *
 * The method symbol contains a value that is associated with this method
 * throughout the whole tag, in the range $80-F0. All other values are reserved.
 * The method symbol may optionally be followed by encryption specific data.
 *
 * There may be several ENCR frames in a tag but only one containing the same
 * symbol and only one containing the same owner identifier. The method must be
 * used somewhere in the tag. See {@link ID3_Frame#ENCRYPTION} for more
 * information.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_ENCR extends ID3_Frame
{
  /** @var string */
  private $_id;
  
  /** @var integer */
  private $_method;
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    list($this->_id, $this->_data) = preg_split("/\\x00/", $this->_data, 2);
    $this->_method = substr($this->_data, 0, 1);
    $this->_data = substr($this->_data, 1);
  }

  /**
   * Returns the owner identifier string.
   * 
   * @return string
   */
  public function getIdentifier() { return $this->_id; }

  /**
   * Returns the method symbol.
   * 
   * @return integer
   */
  public function getMethod() { return $this->_method; }

  /**
   * Returns the encryption data.
   * 
   * @return string
   */
  public function getData() { return $this->_data; }
}
