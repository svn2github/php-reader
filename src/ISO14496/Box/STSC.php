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
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * Samples within the media data are grouped into chunks. Chunks can be of
 * different sizes, and the samples within a chunk can have different sizes.
 * The <i>Sample To Chunk Box</i> table can be used to find the chunk that
 * contains a sample, its position, and the associated sample description.
 *
 * The table is compactly coded. Each entry gives the index of the first chunk
 * of a run of chunks with the same characteristics. By subtracting one entry
 * here from the previous one, you can compute how many chunks are in this run.
 * You can convert this to a sample count by multiplying by the appropriate
 * samplesPerChunk.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ISO14496_Box_STSC extends ISO14496_Box_Full
{
  /** @var Array */
  private $_sampleToChunkTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);
    
    $entryCount = $this->_reader->readUInt32BE();
    for ($i = 1; $i < $entryCount; $i++)
      $this->_sampleToChunkTable[$i] = array
        ("firstChunk" => $this->_reader->readUInt32BE(),
         "samplesPerChunk" => $this->_reader->readUInt32BE(),
         "sampleDescriptionIndex" => $this->_reader->readUInt32BE());
  }
  
  /**
   * Returns an array of values. Each entry is an array containing the following
   * keys.
   *   o firstChunk -- an integer that gives the index of the first chunk in
   *     this run of chunks that share the same samplesPerChunk and
   *     sampleDescriptionIndex; the index of the first chunk in a track has the
   *     value 1 (the firstChunk field in the first record of this box has the
   *     value 1, identifying that the first sample maps to the first chunk).
   *   o samplesPerChunk is an integer that gives the number of samples in each
   *     of these chunks.
   *   o sampleDescriptionIndex is an integer that gives the index of the sample
   *     entry that describes the samples in this chunk. The index ranges from 1
   *     to the number of sample entries in the {@link ISO14496_Box_STSD Sample
   *     Description Box}.
   *
   * @return Array
   */
  public function getSampleToChunkTable()
  {
    return $this->_sampleToChunkTable;
  }
}
