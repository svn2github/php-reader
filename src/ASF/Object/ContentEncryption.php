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
 * @subpackage ASF
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Content Encryption Object</i> lets authors protect content by using
 * Microsoft® Digital Rights Manager version 1.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_ContentEncryption extends ASF_Object
{
  /** @var string */
  private $_secretData;
  
  /** @var string */
  private $_protectionType;
  
  /** @var string */
  private $_keyId;
  
  /** @var string */
  private $_licenseUrl;
  
  /**
   * Constructs the class with given parameters and reads object related data
   * from the ASF file.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader = null, &$options = array())
  {
    parent::__construct($reader, $options);
    
    if ($reader === null)
      return;
    
    $secretDataLength = $this->_reader->readUInt32LE();
    $this->_secretData = $this->_reader->read($secretDataLength);
    $protectionTypeLength = $this->_reader->readUInt32LE();
    $this->_protectionType = $this->_reader->readString8($protectionTypeLength);
    $keyIdLength = $this->_reader->readUInt32LE();
    $this->_keyId = $this->_reader->readString8($keyIdLength);
    $licenseUrlLength = $this->_reader->readUInt32LE();
    $this->_licenseUrl = $this->_reader->readString8($licenseUrlLength);
  }
  
  /**
   * Returns the secret data.
   *
   * @return string
   */
  public function getSecretData() { return $this->_secretData; }
  
  /**
   * Sets the secret data.
   * 
   * @param string $secretData The secret data.
   */
  public function setSecretData($secretData)
  {
    $this->_secretData = $secretData;
  }
  
  /**
   * Returns the type of protection mechanism used. The value of this field
   * is set to "DRM".
   *
   * @return string
   */
  public function getProtectionType() { return $this->_protectionType; }
  
  /**
   * Sets the type of protection mechanism used. The value of this field
   * is to be set to "DRM".
   * 
   * @param string $protectionType The protection mechanism used.
   */
  public function setProtectionType($protectionType)
  {
    $this->_protectionType = $protectionType;
  }
  
  /**
   * Returns the key ID used.
   *
   * @return string
   */
  public function getKeyId() { return $this->_keyId; }
  
  /**
   * Sets the key ID used.
   * 
   * @param string $keyId The key ID used.
   */
  public function setKeyId($keyId) { $this->_keyId = $keyId; }
  
  /**
   * Returns the URL from which a license to manipulate the content can be
   * acquired.
   *
   * @return string
   */
  public function getLicenseUrl() { return $this->_licenseUrl; }
  
  /**
   * Returns the URL from which a license to manipulate the content can be
   * acquired.
   * 
   * @param string $licenseUrl The URL from which a license can be acquired.
   */
  public function setLicenseUrl($licenseUrl)
  {
    $this->_licenseUrl = $licenseUrl;
  }
  
  /**
   * Returns the whether the object is required to be present, or whether
   * minimum cardinality is 1.
   * 
   * @return boolean
   */
  public function isMandatory() { return false; }
  
  /**
   * Returns whether multiple instances of this object can be present, or
   * whether maximum cardinality is greater than 1.
   * 
   * @return boolean
   */
  public function isMultiple() { return false; }
  
  /**
   * Returns the object data with headers.
   *
   * @return string
   */
  public function __toString()
  {
    $data =
      Transform::toUInt32LE(strlen($this->_secretData)) .
      $this->_secretData .
      Transform::toUInt32LE($len = strlen($this->_protectionType) + 1) .
      Transform::toString8($this->_protectionType, $len) .
      Transform::toUInt32LE($len = strlen($this->_keyId) + 1) .
      Transform::toString8($this->_keyId, $len) .
      Transform::toUInt32LE($len = strlen($this->_licenseUrl) + 1) .
      Transform::toString8($this->_licenseUrl, $len);
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
