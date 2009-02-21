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

/**
 * The base unit of organization for ASF files is called the ASF object. It
 * consists of a 128-bit GUID for the object, a 64-bit integer object size, and
 * the variable-length object data.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2006-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
abstract class ASF_Object
{
  /* ASF Objects */
  const HEADER = "75b22630-668e-11cf-a6d9-00aa0062ce6c";
  const DATA = "75b22636-668e-11cf-a6d9-00aa0062ce6c";
  const SIMPLE_INDEX = "33000890-e5b1-11cf-89f4-00a0c90349cb";
  const INDEX = "d6e229d3-35da-11d1-9034-00a0c90349be";
  const MEDIA_OBJECT_INDEX = "feb103f8-12ad-4c64-840f-2a1d2f7ad48c";
  const TIMECODE_INDEX = "3cb73fd0-0c4a-4803-953d-edf7b6228f0c";
  
  /* Header Objects */
  const FILE_PROPERTIES = "8cabdca1-a947-11cf-8ee4-00c00c205365";
  const STREAM_PROPERTIES = "b7dc0791-a9b7-11cf-8ee6-00c00c205365";
  const HEADER_EXTENSION = "5fbf03b5-a92e-11cf-8ee3-00c00c205365";
  const CODEC_LIST = "86d15240-311d-11d0-a3a4-00a0c90348f6";
  const SCRIPT_COMMAND = "1efb1a30-0b62-11d0-a39b-00a0c90348f6";
  const MARKER = "f487cd01-a951-11cf-8ee6-00c00c205365";
  const BITRATE_MUTUAL_EXCLUSION = "d6e229dc-35da-11d1-9034-00a0c90349be";
  const ERROR_CORRECTION = "75b22635-668e-11cf-a6d9-00aa0062ce6c";
  const CONTENT_DESCRIPTION = "75b22633-668e-11cf-a6d9-00aa0062ce6c";
  const EXTENDED_CONTENT_DESCRIPTION = "d2d0a440-e307-11d2-97f0-00a0c95ea850";
  const CONTENT_BRANDING = "2211b3fa-bd23-11d2-b4b7-00a0c955fc6e";
  const STREAM_BITRATE_PROPERTIES = "7bf875ce-468d-11d1-8d82-006097c9a2b2";
  const CONTENT_ENCRYPTION = "2211b3fb-bd23-11d2-b4b7-00a0c955fc6e";
  const EXTENDED_CONTENT_ENCRYPTION = "298ae614-2622-4c17-b935-dae07ee9289c";
  const DIGITAL_SIGNATURE = "2211b3fc-bd23-11d2-b4b7-00a0c955fc6e";
  const PADDING = "1806d474-cadf-4509-a4ba-9aabcb96aae8";
  
  /* Header Extension Objects */
  const EXTENDED_STREAM_PROPERTIES = "14e6a5cb-c672-4332-8399-a96952065b5a";
  const ADVANCED_MUTUAL_EXCLUSION = "a08649cf-4775-4670-8a16-6e35357566cd";
  const GROUP_MUTUAL_EXCLUSION = "d1465a40-5a79-4338-b71b-e36b8fd6c249";
  const STREAM_PRIORITIZATION  = "d4fed15b-88d3-454f-81f0-ed5c45999e24";
  const BANDWIDTH_SHARING  = "a69609e6-517b-11d2-b6af-00c04fd908e9";
  const LANGUAGE_LIST  = "7c4346a9-efe0-4bfc-b229-393ede415c85";
  const METADATA  = "c5f8cbea-5baf-4877-8467-aa8c44fa4cca";
  const METADATA_LIBRARY = "44231c94-9498-49d1-a141-1d134e457054";
  const INDEX_PARAMETERS  = "d6e229df-35da-11d1-9034-00a0c90349be";
  const MEDIA_OBJECT_INDEX_PARAMETERS = "6b203bad-3f11-48e4-aca8-d7613de2cfa7";
  const TIMECODE_INDEX_PARAMETERS = "f55e496d-9797-4b5d-8c8b-604dfe9bfb24";
  const COMPATIBILITY = "75b22630-668e-11cf-a6d9-00aa0062ce6c";
  const ADVANCED_CONTENT_ENCRYPTION = "43058533-6981-49e6-9b74-ad12cb86d58c";
  
  /**
   * The reader object.
   *
   * @var Reader
   */
  protected $_reader;
  
  /**
   * The options array.
   * 
   * @var Array
   */
  protected $_options;

  /** @var integer */
  private $_offset = false;
  
  /** @var string */
  private $_identifier = false;
  
  /** @var integer */
  private $_size = false;
  
  /** @var ASF_Object */
  private $_parent = null;
  
  /**
   * Constructs the class with given parameters and options.
   *
   * @param Reader $reader  The reader object.
   * @param Array  $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    $this->_reader = $reader;
    $this->_options = &$options;
    
    if ($reader === null) {
      if (defined($constant = "self::" . strtoupper
                  (preg_replace
                   ("/(?<=[a-z])[A-Z]/", "_$0", substr(get_class($this), 11)))))
        $this->_identifier = constant($constant);
      else {
        require_once("ASF/Exception.php");
        throw new ASF_Exception("Object identifier could not be determined");
      }
    } else {
      $this->_offset = $this->_reader->getOffset();
      $this->_identifier = $this->_reader->readGUID();
      $this->_size = $this->_reader->readInt64LE();
    }
  }
  
  /**
   * Returns the file offset to object start, or <var>false</var> if the object
   * was created on heap.
   * 
   * @return integer
   */
  public final function getOffset() { return $this->_offset; }
  
  /**
   * Sets the file offset where the object starts.
   * 
   * @param integer $offset The file offset to object start.
   */
  public final function setOffset($offset) { $this->_offset = $offset; }
  
  /**
   * Returns the GUID of the ASF object.
   * 
   * @return string
   */
  public final function getIdentifier() { return $this->_identifier; }
  
  /**
   * Set the GUID of the ASF object.
   * 
   * @param string $id The GUID
   */
  public final function setIdentifier($identifier)
  {
    $this->_identifier = $identifier;
  }
  
  /**
   * Returns the object size in bytes, including the header.
   * 
   * @return integer
   */
  public final function getSize() { return $this->_size; }
  
  /**
   * Sets the object size. The size must include the 24 byte header.
   * 
   * @param integer $size The object size.
   */
  public final function setSize($size)
  {
    if ($this->_parent !== null)
      $this->_parent->setSize
        (($this->_parent->getSize() > 0 ? $this->_parent->getSize() : 0) +
         $size - ($this->_size > 0 ? $this->_size : 0));
    $this->_size = $size;
  }
  
  /**
   * Returns the options array.
   *
   * @return Array
   */
  public final function getOptions() { return $this->_options; }

  /**
   * Returns the given option value, or the default value if the option is not
   * defined.
   *
   * @param string $option The name of the option.
   * @param mixed $defaultValue The default value to be returned.
   */
  public final function getOption($option, $defaultValue = false)
  {
    if (isset($this->_options[$option]))
      return $this->_options[$option];
    return $defaultValue;
  }
  
  /**
   * Sets the options array. See {@link ASF} class for available options.
   *
   * @param Array $options The options array.
   */
  public final function setOptions(&$options) { $this->_options = &$options; }
  
  /**
   * Sets the given option the given value.
   *
   * @param string $option The name of the option.
   * @param mixed $value The value to set for the option.
   */
  public final function setOption($option, $value)
  {
    $this->_options[$option] = $value;
  }
  
  /**
   * Returns the parent object containing this object.
   * 
   * @return ASF_Object
   */
  public final function getParent() { return $this->_parent; }
  
  /**
   * Sets the parent containing object.
   * 
   * @param ASF_Object $parent The parent object.
   */
  public final function setParent(&$parent) { $this->_parent = $parent; }
  
  /**
   * Magic function so that $obj->value will work.
   *
   * @param string $name The field name.
   * @return mixed
   */
  public function __get($name)
  {
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    if (method_exists($this, "is" . ucfirst($name)))
      return call_user_func(array($this, "is" . ucfirst($name)));
    require_once("ASF/Exception.php");
    throw new ASF_Exception("Unknown field: " . $name);
  }
  
  /**
   * Magic function so that assignments with $obj->value will work.
   *
   * @param string $name  The field name.
   * @param string $value The field value.
   * @return mixed
   */
  public function __set($name, $value)
  {
    if (method_exists($this, "set" . ucfirst($name)))
      call_user_func(array($this, "set" . ucfirst($name)), $value);
    else {
      require_once("ASF/Exception.php");
      throw new ASF_Exception("Unknown field: " . $name);
    }
  }
  
  /**
   * Returns the whether the object is required to be present, or whether
   * minimum cardinality is 1.
   * 
   * @return boolean
   */
  public abstract function isMandatory();
  
  /**
   * Returns whether multiple instances of this object can be present, or
   * whether maximum cardinality is greater than 1.
   * 
   * @return boolean
   */
  public abstract function isMultiple();
}
