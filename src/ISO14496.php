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
require_once("Reader.php");
require_once("ISO14496/Box.php");
/**#@-*/

/**
 * This class represents a file in ISO base media file format as described in
 * ISO/IEC 14496 Part 12 standard.
 *
 * The ISO Base Media File Format is designed to contain timed media information
 * for a presentation in a flexible, extensible format that facilitates
 * interchange, management, editing, and presentation of the media. This
 * presentation may be local to the system containing the presentation, or may
 * be via a network or other stream delivery mechanism.
 *
 * The file structure is object-oriented; a file can be decomposed into
 * constituent objects very simply, and the structure of the objects inferred
 * directly from their type. The file format is designed to be independent of
 * any particular network protocol while enabling efficient support for them in
 * general.
 *
 * The ISO Base Media File Format is a base format for media file formats.
 * 
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
class ISO14496 extends ISO14496_Box
{
  /**
   * Constructs the ISO14496 class with given file.
   *
   * @param string $filename The path to the file or file descriptor of an
   *                         opened file.
   */
  public function __construct($filename)
  {
    $this->_reader = new Reader($filename);
    $this->_offset = 0;
    $this->_size = $this->_reader->getSize();
    $this->_type = "file";
    $this->_container = true;
    $this->constructBoxes();
  }
}
