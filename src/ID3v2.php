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
require_once("Reader.php");
require_once("ID3/Exception.php");
require_once("ID3/Header.php");
require_once("ID3/ExtendedHeader.php");
require_once("ID3/Frame.php");
/**#@-*/

/**
 * This class represents a file containing ID3v2 headers as described in
 * {@link http://www.id3.org/id3v2.4.0-structure ID3v2 structure document}.
 *
 * ID3v2 is a general tagging format for audio, which makes it possible to store
 * meta data about the audio inside the audio file itself. The ID3 tag is mainly
 * targeted at files encoded with MPEG-1/2 layer I, MPEG-1/2 layer II, MPEG-1/2
 * layer III and MPEG-2.5, but may work with other types of encoded audio or as
 * a stand alone format for audio meta data.
 *
 * ID3v2 is designed to be as flexible and expandable as possible to meet new
 * meta information needs that might arise. To achieve that ID3v2 is constructed
 * as a container for several information blocks, called frames, whose format
 * need not be known to the software that encounters them. Each frame has an
 * unique and predefined identifier which allows software to skip unknown
 * frames.
 *
 * Overall tag structure:
 *
 * <pre>
 *   +-----------------------------+
 *   |      Header (10 bytes)      |
 *   +-----------------------------+
 *   |       Extended Header       |
 *   | (variable length, OPTIONAL) |
 *   +-----------------------------+
 *   |   Frames (variable length)  |
 *   +-----------------------------+
 *   |           Padding           |
 *   | (variable length, OPTIONAL) |
 *   +-----------------------------+
 *   | Footer (10 bytes, OPTIONAL) |
 *   +-----------------------------+
 * </pre>
 * 
 * In general, padding and footer are mutually exclusive.
 * 
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ID3v2
{
  /** @var Reader */
  private $_reader;

  /** @var ID3_Header */
  private $_header;

  /** @var ID3_ExtendedHeader */
  private $_extendedHeader;
  
  /** @var ID3_Header */
  private $_footer = null;
  
  /** @var Array */
  private $_frames = array();
  
  /** @var string */
  private $_filename;

  /**
   * Constructs the ID3v2 class with given file and options.
   *
   * @todo  Only limited subset of flags are processed.
   * @todo  ID3_Footer
   * @param string $filename The path to the file.
   * @param Array  $options  The options array.
   */
  public function __construct($filename = false, $options = array())
  {
    if (is_array($filename)) {
      $options = $filename;
      $filename = false;
    }
    
    if (($this->_filename = $filename) === false ||
        file_exists($filename) === false)
      return;
    
    $this->_reader = new Reader($filename);
    
    if ($this->_reader->readString8(3) != "ID3")
      throw new ID3_Exception("File does not contain ID3v2 tag: " . $filename);
    
    $this->_header = new ID3_Header($this->_reader);
    if ($this->_header->getVersion() > 4)
      throw new ID3_Exception
        ("File does not contain ID3v2 tag of supported version: " . $filename);
    if ($this->_header->hasFlag(ID3_Header::EXTENDEDHEADER))
      $this->_extendedHeader = new ID3_ExtendedHeader($this->_reader);
    if ($this->_header->hasFlag(ID3_Header::FOOTER)) {
      $offset = $this->_reader->offset;
      $this->_reader->offset = $this->_header->getSize() + 10;
      $this->_footer = new ID3_Header($this->_reader);
      $this->_reader->offset = $offset;
    }
    
    while ($frame = $this->nextFrame()) {
      if (!isset($this->_frames[$frame->identifier]))
        $this->_frames[$frame->identifier] = array();
      $this->_frames[$frame->identifier][] = $frame;
    }
  }

  /**
   * Returns the header object.
   * 
   * @return ID3_Header
   */
  public function getHeader() { return $this->_header; }
  
  /**
   * Checks whether there is an extended header present in the tag. Returns
   * <var>true</var> if the header is present, <var>false</var> otherwise.
   * 
   * @return boolean
   */
  public function hasExtendedHeader()
  {
    return $this->_header->hasFlag(ID3_Header::EXTENDEDHEADER);
  }
  
  /**
   * Returns the extended header object if present, or <var>false</var>
   * otherwise.
   * 
   * @return ID3_ExtendedHeader|false
   */
  public function getExtendedHeader()
  {
    if ($this->hasExtendedHeader())
      return $this->_extendedHeader;
    return false;
  }

  /**
   * Checks whether there are frames left in the tag. Returns <var>true</var> if
   * there are frames left in the tag, <var>false</var> otherwise.
   * 
   * @return boolean
   */
  protected function hasFrames()
  {
    $offset = $this->_reader->offset;
    
    // Return false if we reached the end of the tag
    if ($offset >= $this->_header->getSize() - 10 -
        ($this->hasFooter() ? 10 : 0))
      return false;
    
    // Return false if we reached the last frame, true otherwise
    $res = $this->_reader->readUInt32BE() != 0;
    $this->_reader->offset = $offset;
    return $res;
  }
  
  /**
   * Returns the next ID3 frame or <var>false</var> if end of tag has been
   * reached. Returned objects are of the type ID3_Frame or of any of its child
   * types.
   * 
   * @return ID3_Frame|false
   */
  protected function nextFrame()
  {
    $frame = false;
    if ($this->hasFrames()) {
      $offset = $this->_reader->offset;
      $identifier = $this->_reader->readString8(4);
      $this->_reader->offset = $offset;
      if (file_exists($filename = "ID3/Frame/" . $identifier . ".php"))
        require_once($filename);
      if (class_exists($classname = "ID3_Frame_" . $identifier))
        $frame = new $classname($this->_reader);
      else
        $frame = new ID3_Frame($this->_reader);
    }
    return $frame;
  }
  
  /**
   * Checks whether there is a frame given as an argument defined in the tag.
   * Returns <var>true</var> if one ore more frames are present,
   * <var>false</var> otherwise.
   * 
   * @return boolean
   */
  public function hasFrame($identifier)
  {
    return isset($this->_frames[$identifier]);
  }
  
  /**
   * Returns all the frames the tag contains as an associate array. The frame
   * identifiers work as keys having an array of frames as associated value.
   * 
   * @return Array
   */
  public function getFrames()
  {
    return $this->_frames;
  }
  
  /**
   * Returns an array of frames matching the given identifier or an empty array
   * if no frames matched the identifier.
   *
   * The identifier may contain wildcard characters "*" and "?". The asterisk
   * matches against zero or more characters, and the question mark matches any
   * single character.
   *
   * Please note that one may also use the shorthand $obj->identifier to access
   * the first frame with the identifier given. Wildcards cannot be used with
   * the shorthand.
   * 
   * @return Array
   */
  public function getFramesByIdentifier($identifier)
  {
    $matches = array();
    $searchPattern = "/^" .
      str_replace(array("*", "?"), array(".*", "."), $identifier) . "$/i";
    foreach ($this->_frames as $identifier => $frames)
      if (preg_match($searchPattern, $identifier))
        foreach ($frames as $frame)
          $matches[] = $frame;
    return $matches;
  }
  
  /**
   * Checks whether there is a footer present in the tag. Returns
   * <var>true</var> if the footer is present, <var>false</var> otherwise.
   * 
   * @return boolean
   */
  public function hasFooter()
  {
    return $this->_header->hasFlag(ID3_Header::FOOTER);
  }
  
  /**
   * Returns the footer object if present, or <var>false</var> otherwise.
   *
   * @return ID3_Header|false
   */
  public function getFooter()
  {
    if ($this->hasFooter())
      return $this->_footer;
    return false;
  }
  
  /**
   * Magic function so that $obj->value will work. The method will attempt to
   * return the first frame that matches the identifier.
   *
   * @param string $name The frame or field name.
   * @return mixed
   */
  public function __get($name) {
    if (isset($this->_frames[$name]))
      return $this->_frames[$name][0];
    if (method_exists($this, "get" . ucfirst($name)))
      return call_user_func(array($this, "get" . ucfirst($name)));
    else throw new ID3_Exception("Unknown frame/field: " . $name);
  }
}
