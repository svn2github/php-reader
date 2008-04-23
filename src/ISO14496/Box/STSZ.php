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
 * The <i>Sample Size Box</i> contains the sample count and a table giving the
 * size in bytes of each sample. This allows the media data itself to be
 * unframed. The total number of samples in the media is always indicated in the
 * sample count.
 *
 * There are two variants of the sample size box. The first variant has a fixed
 * size 32-bit field for representing the sample sizes; it permits defining a
 * constant size for all samples in a track. The second variant permits smaller
 * size fields, to save space when the sizes are varying but small. One of these
 * boxes must be present; the first version is preferred for maximum
 * compatibility.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ISO14496_Box_STSZ extends ISO14496_Box_Full
{
  /** @var integer */
  private $_sampleSize;
  
  /** @var Array */
  private $_sampleSizeTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);
    
    $this->_sampleSize = $this->_reader->readUInt32BE();
    $sampleCount = $this->_reader->readUInt32BE();
    if ($this->_sampleSize == 0)
      for ($i = 1; $i < $sampleCount; $i++)
        $this->_sampleSizeTable[$i] = $this->_reader->readUInt32BE();
  }
  
  /**
   * Returns the default sample size. If all the samples are the same size, this
   * field contains that size value. If this field is set to 0, then the samples
   * have different sizes, and those sizes are stored in the sample size table.
   *
   * @return integer
   */
  public function getSampleSize() { return $this->_sampleSize; }
  
  /**
   * Returns an array of sample sizes specifying the size of a sample, indexed
   * by its number.
   *
   * @return Array
   */
  public function getSampleSizeTable()
  {
    return $this->_sampleSizeTable;
  }
}
