<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2006-2009 The PHP Reader Project Workgroup. All rights
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
 * @copyright  Copyright (c) 2006-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ASF/Object.php");
/**#@-*/

/**
 * The <i>Content Description Object</i> lets authors record well-known data
 * describing the file and its contents. This object is used to store standard
 * bibliographic information such as title, author, copyright, description, and
 * rating information. This information is pertinent to the entire file.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_ContentDescription extends ASF_Object
{
  /** @var string */
  private $_title;

  /** @var string */
  private $_author;

  /** @var string */
  private $_copyright;

  /** @var string */
  private $_description;

  /** @var string */
  private $_rating;

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
    
    $titleLen = $this->_reader->readUInt16LE();
    $authorLen = $this->_reader->readUInt16LE();
    $copyrightLen = $this->_reader->readUInt16LE();
    $descriptionLen = $this->_reader->readUInt16LE();
    $ratingLen = $this->_reader->readUInt16LE();

    $this->_title = iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16($titleLen));
    $this->_author =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16($authorLen));
    $this->_copyright =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16($copyrightLen));
    $this->_description =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16($descriptionLen));
    $this->_rating =  iconv
      ("utf-16le", $this->getOption("encoding"),
       $this->_reader->readString16($ratingLen));
  }
  
  /**
   * Returns the title information.
   *
   * @return string
   */
  public function getTitle() { return $this->_title; }
  
  /**
   * Sets the title information.
   *
   * @param string $title The title information.
   */
  public function setTitle($title) { $this->_title = $title; }
  
  /**
   * Returns the author information.
   *
   * @return string
   */
  public function getAuthor() { return $this->_author; }
  
  /**
   * Sets the author information.
   *
   * @param string $author The author information.
   */
  public function setAuthor($author) { $this->_author = $author; }
  
  /**
   * Returns the copyright information.
   *
   * @return string
   */
  public function getCopyright() { return $this->_copyright; }
  
  /**
   * Sets the copyright information.
   *
   * @param string $copyright The copyright information.
   */
  public function setCopyright($copyright) { $this->_copyright = $copyright; }
  
  /**
   * Returns the description information.
   *
   * @return string
   */
  public function getDescription() { return $this->_description; }
  
  /**
   * Sets the description information.
   *
   * @param string $description The description information.
   */
  public function setDescription($description)
  {
    $this->_description = $description;
  }
  
  /**
   * Returns the rating information.
   *
   * @return string
   */
  public function getRating() { return $this->_rating; }
  
  /**
   * Sets the rating information.
   *
   * @param string $rating The rating information.
   */
  public function setRating($rating) { $this->_rating = $rating; }
  
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
    $title = iconv
      ($this->getOption("encoding"), "utf-16le",
       $this->_title ? $this->_title . "\0" : "");
    $author =  iconv
      ($this->getOption("encoding"), "utf-16le",
       $this->_author ? $this->_author . "\0" : "");
    $copyright =  iconv
      ($this->getOption("encoding"), "utf-16le",
       $this->_copyright ? $this->_copyright . "\0" : "");
    $description =  iconv
      ($this->getOption("encoding"), "utf-16le",
       $this->_description ? $this->_description . "\0" : "");
    $rating =  iconv
      ($this->getOption("encoding"), "utf-16le",
       $this->_rating ? $this->_rating . "\0" : "");
    
    $data =
      Transform::toUInt16LE(strlen($title)) .
      Transform::toUInt16LE(strlen($author)) .
      Transform::toUInt16LE(strlen($copyright)) .
      Transform::toUInt16LE(strlen($description)) .
      Transform::toUInt16LE(strlen($rating)) .
      Transform::toString16($title) .
      Transform::toString16($author) .
      Transform::toString16($copyright) .
      Transform::toString16($description) .
      Transform::toString16($rating);
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
