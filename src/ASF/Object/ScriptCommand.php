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
 * The <i>Script Command Object</i> provides a list of type/parameter pairs of
 * strings that are synchronized to the ASF file's timeline. Types can include
 * URL or FILENAME values. Other type values may also be freely defined and
 * used. The semantics and treatment of this set of types are defined by the
 * local implementations. The parameter value is specific to the type field. You
 * can use this type/parameter pairing for many purposes, including sending URLs
 * to be launched by a client into an HTML frame (in other words, the URL type)
 * or launching another ASF file for the chained continuous play of audio or
 * video presentations (in other words, the FILENAME type). This object is also
 * used as a method to stream text, as well as to provide script commands that
 * you can use to control elements within the client environment.
 *
 * @package    php-reader
 * @subpackage ASF
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008-2009 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ASF_Object_ScriptCommand extends ASF_Object
{
  /** @var string */
  private $_reserved;
  
  /** @var Array */
  private $_commands = array();
  
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
    
    $this->_reserved = $this->_reader->readGUID();
    $commandsCount = $this->_reader->readUInt16LE();
    $commandTypesCount = $this->_reader->readUInt16LE();
    $commandTypes = array();
    for ($i = 0; $i < $commandTypesCount; $i++) {
      $commandTypeNameLength = $this->_reader->readUInt16LE();
      $commandTypes[] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16($commandTypeNameLength * 2));
    }
    for ($i = 0; $i < $commandsCount; $i++) {
      $command = array
        ("presentationTime" => $this->_reader->readUInt32LE(),
         "type" => $commandTypes[$this->_reader->readUInt16LE()]);
      $commandNameLength = $this->_reader->readUInt16LE();
      $command["name"] = iconv
        ("utf-16le", $this->getOption("encoding"),
         $this->_reader->readString16($commandNameLength * 2));
      $this->_commands[] = $command;
    }
  }

  /**
   * Returns an array of index entries. Each entry consists of the following
   * keys.
   * 
   *   o presentationTime -- Specifies the presentation time of the command, in
   *     milliseconds.
   * 
   *   o type -- Specifies the type of this command.
   * 
   *   o name -- Specifies the name of this command.
   *
   * @return Array
   */
  public function getCommands() { return $this->_commands; }

  /**
   * Sets the array of index entries. Each entry is to consist of the following
   * keys.
   * 
   *   o presentationTime -- Specifies the presentation time of the command, in
   *     milliseconds.
   * 
   *   o type -- Specifies the type of this command.
   * 
   *   o name -- Specifies the name of this command.
   * 
   * @param Array $commands The array of index entries.
   */
  public function setCommands($commands) { $this->_commands = $commands; }
  
  /**
   * Returns the object data with headers.
   *
   * @return string
   */
  public function __toString()
  {
    $commandTypes = array();
    foreach ($this->_commands as $command)
      if (!in_array($command["type"], $commandTypes))
        $commandTypes[] = $command["type"];
    $data = 
      Transform::toGUID($this->_reserved) .
      Transform::toUInt16LE($commandsCount = count($this->_commands)) .
      Transform::toUInt16LE($commandTypesCount = count($commandTypes));
    for ($i = 0; $i < $commandTypesCount; $i++)
      $data .=
        Transform::toUInt16LE
          (strlen($commandType = iconv
           ($this->getOption("encoding"), "utf-16le",
            $commandTypes[$i])) / 2) . $commandType;
    for ($i = 0; $i < $commandsCount; $i++)
      $data .=
        Transform::toUInt32LE($this->_commands[$i]["presentationTime"]) .
        Transform::toUInt16LE
          (array_search($this->_commands[$i]["type"], $commandTypes)) .
        Transform::toUInt16LE
          (strlen($command = iconv
           ($this->getOption("encoding"), "utf-16le",
            $this->_commands[$i]["name"])) / 2) . $command;
    $this->setSize(24 /* for header */ + strlen($data));
    return
      Transform::toGUID($this->getIdentifier()) .
      Transform::toInt64LE($this->getSize())  . $data;
  }
}
