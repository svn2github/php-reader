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
 * @package   php-reader
 * @copyright Copyright (c) 2006, 2007 The Bearpaw Project Work Group
 * @copyright Copyright (c) 2007, 2008 BEHR Software Systems
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   $Id$
 */

/**#@+ @ignore */
require_once("Reader/Exception.php");
require_once("Transform.php");
/**#@-*/

/**
 * The Reader class encapsulates a file. It is hence responsible of upkeeping
 * the connection to the file, keeping track of the cursor position and reading
 * data from it.
 * 
 * @package   php-reader
 * @author    Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright Copyright (c) 2006, 2007 The Bearpaw Project Work Group
 * @copyright Copyright (c) 2007, 2008 BEHR Software Systems
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   $Rev$
 */
class Reader
{
  /** @var resource */
  private $_fd;
  
  /** @var integer */
  private $_size;
  
  /**
   * Constructs the Reader class with given file.
   * 
   * @param string $filename The path to the file.
   * @throws Reader_Exception if the file cannot be read.
   */
  public function __construct($filename)
  {
    if (($this->_fd = fopen($filename, "rb")) === false)
      throw new Reader_Exception("Unable to open file:" . $filename);
    
    fseek($this->_fd, 0, SEEK_END);
    $this->_size = ftell($this->_fd);
    fseek($this->_fd, 0);
  }
  
  /**
   * Closes the file.
   */
  public function __destruct()
  {
    @fclose($this->_fd);
  }
  
  /**
   * Checks whether there is more to be read in the file. Returns
   * <var>true</var> if the end of the file has not yet been reached;
   * <var>false</var> otherwise.
   * 
   * @return boolean 
   */
  public function available()
  {
    return $this->getOffset() < $this->_size;
  }
  
  /**
   * Jumps <var>size</var> amount of bytes in the file stream.
   * 
   * @param integer $size The amount of bytes.
   * @return void
   * @throws Reader_Exception if <var>size</var> attribute is negative.
   */
  public function skip($size)
  {
    if ($size < 0)
      throw new Reader_Exception("Invalid argument");
    if ($size == 0)
      return;
    fseek($this->_fd, $size, SEEK_CUR);
  }
  
  /**
   * Reads <var>length</var> amount of bytes from the file stream.
   * 
   * @param integer $length The amount of bytes.
   * @return string
   * @throws Reader_Exception if <var>length</var> attribute is negative.
   */
  public function read($length)
  {
    if ($length < 0)
      throw new Reader_Exception("Invalid argument");
    if ($length == 0)
      return "";
    return fread($this->_fd, $length);
  }
  
  /**
   * Returns the current point of operation.
   * 
   * @return integer
   */
  public function getOffset()
  {
    return ftell($this->_fd);
  }

  /**
   * Sets the point of operation, ie the cursor offset value. The offset can
   * also be set to a negative value when it is interpreted as an offset from
   * the end of the file instead of the beginning.
   * 
   * @param integer $offset The new point of operation.
   * @return void
   */
  public function setOffset($offset)
  {
    fseek($this->_fd, $offset < 0 ? $this->_size + $offset : $offset);
  }
  
  /**
   * Returns the file size in bytes.
   * 
   * @return integer
   */
  public function getSize()
  {
    return $this->_size;
  }

  /**
   * Magic function to delegate the call to helper methods of
   * <var>Transform</var> class to transform read data in another format.
   *
   * The read data length is determined from the helper method name. For methods
   * where arbitrary data lengths are accepted a parameter can be used to
   * specify the length.
   *
   * @param string $method The method to be called.
   * @param string $params The parameters should the function accept them.
   * @return mixed
   * @throws Reader_Exception if no such transformer is implemented
   */
  public function __call($method, $params) {
    $chunks = array();
    if (preg_match
          ("/get([a-z]{3,6})?(\d{1,2})?(?:LE|BE)?/i", $method, $chunks) &&
        method_exists("Transform", $method)) {
      return call_user_func
        (array("Transform", $method),
         $this->read(preg_match("/String|(?:H|L)Hex/", $chunks[1]) ?
                     (isset($params[0]) ? $params[0] : 1) :
                     ($chunks[1] == "GUID" ? 16 : $chunks[2] / 8)));
    } else throw new Reader_Exception("Unknown method: " . $method);
  }
}
