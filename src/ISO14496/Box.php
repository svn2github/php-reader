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
 * @copyright  Copyright (c) 2008 PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ISO14496/Exception.php");
/**#@-*/

/**
 * A base class for all ISO 14496-12 boxes.
 * 
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
class ISO14496_Box
{
  /**
   * The reader object.
   *
   * @var Reader
   */
  protected $_reader;
  
  /**
   * The file offset to box start.
   *
   * @var integer
   */
  protected $_offset;
  
  /**
   * The object size in bytes, including the size and type header, fields, and
   * all contained boxes.
   *
   * @var integer
   */
  protected $_size;
  
  /** @var string */
  protected $_type;
  
  
  /** @var boolean */
  protected $_container = false;
  
  /**
   * An array of boxes the box contains.
   *
   * @var Array
   */
  protected $_boxes = array();
  
  
  /**
   * Constructs the class with given parameters.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    $this->_reader = $reader;
    $this->_offset = $this->_reader->getOffset();
    $this->_size = $this->_reader->readUInt32BE();
    $this->_type = $this->_reader->read(4);
    
    if ($this->_size == 1)
      $this->_size = $this->_reader->readInt64BE();
    if ($this->_size == 0)
      $this->_size = $this->_reader->getSize() - $offset;

    if ($this->_type == "uuid")
      $this->_type = $this->_reader->readGUID();
  }
  
  /**
   * Returns the type of the ISO base media file object.
   * 
   * @return string
   */
  public function getType() { return $this->_type; }
  
  /**
   * Returns a boolean value corresponding to whether the box is a container.
   * 
   * @return boolean
   */
  public function isContainer() { return $this->_container; }
  
  /**
   * Returns a boolean value corresponding to whether the box is a container.
   * 
   * @return boolean
   */
  public function getContainer() { return $this->_container; }
  
  /**
   * Sets whether the box is a container.
   * 
   * @param boolean $container Whether the box is a container.
   */
  protected function setContainer($container)
  {
    $this->_container = $container;
  }

  /**
   * Reads and constructs the boxes found within this box.
   */
  protected function constructBoxes($defaultclassname = "ISO14496_Box")
  {
    while (true) {
      $offset = $this->_reader->getOffset();
      if ($offset >= $this->_offset + $this->_size)
        break;
      $size = $this->_reader->readUInt32BE();
      $type = $this->_reader->read(4);
      if ($size == 1)
        $size = $this->_reader->readInt64BE();
      if ($size == 0)
        $size = $this->_reader->getSize() - $offset;
      $this->_reader->setOffset($offset);
      
      if (@fopen($filename = "ISO14496/Box/" . strtoupper($type) . ".php",
                 "r", true) !== false)
        require_once($filename);
      if (class_exists($classname = "ISO14496_Box_" . strtoupper($type)))
        $box = new $classname($this->_reader);
      else
        $box = new $defaultclassname($this->_reader);
      
      if (!isset($this->_boxes[$type]))
        $this->_boxes[$type] = array();
      $this->_boxes[$type][] = $box;
      
      $this->_reader->setOffset($offset + $size);
    }
  }
  
  /**
   * Checks whether the box given as an argument is present in the file. Returns
   * <var>true</var> if one or more boxes are present, <var>false</var>
   * otherwise.
   * 
   * @return boolean
   * @throws ISO14496_Exception if called on a non-container box
   */
  public function hasBox($identifier)
  {
    if (!$this->isContainer())
      throw new ISO14496_Exception("Box not a container");
    return isset($this->_boxes[$identifier]);
  }
  
  /**
   * Returns all the boxes the file contains as an associate array. The box
   * identifiers work as keys having an array of boxes as associated value.
   * 
   * @return Array
   * @throws ISO14496_Exception if called on a non-container box
   */
  public function getBoxes()
  {
    if (!$this->isContainer())
      throw new ISO14496_Exception("Box not a container");
    return $this->_boxes;
  }
  
  /**
   * Returns an array of boxes matching the given identifier or an empty array
   * if no boxes matched the identifier.
   *
   * The identifier may contain wildcard characters "*" and "?". The asterisk
   * matches against zero or more characters, and the question mark matches any
   * single character.
   *
   * Please note that one may also use the shorthand $obj->identifier to access
   * the first box with the identifier given. Wildcards cannot be used with
   * the shorthand and they will not work with user defined uuid types.
   * 
   * @return Array
   * @throws ISO14496_Exception if called on a non-container box
   */
  public function getBoxesByIdentifier($identifier)
  {
    if (!$this->isContainer())
      throw new ISO14496_Exception("Box not a container");
    $matches = array();
    $searchPattern = "/^" .
      str_replace(array("*", "?"), array(".*", "."), $identifier) . "$/i";
    foreach ($this->_boxes as $identifier => $boxes)
      if (preg_match($searchPattern, $identifier))
        foreach ($boxes as $box)
          $boxes[] = $box;
    return $boxes;
  }
  
  /**
   * Magic function so that $obj->value will work. If called on a container box,
   * the method will first attempt to return the first contained box that
   * matches the identifier, and if not found, invoke a getter method.
   *
   * If there are no boxes or getter methods with given name, the method
   * will yield an exception.
   *
   * @param string $name The box or field name.
   * @return mixed
   */
  public function __get($name) {
    if ($this->isContainer() && isset($this->_boxes[$name]))
      return $this->_boxes[$name][0];
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    throw new ISO14496_Exception("Unknown box/field: " . $name);
  }
}
