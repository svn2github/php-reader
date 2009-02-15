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
 * @subpackage MPEG
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("MPEG/Object.php");
/**#@-*/

/**
 * The base class for all MPEG Audio Bit Stream objects.
 *
 * @package    php-reader
 * @subpackage MPEG
 * @author     Ryan Butterfield <buttza@gmail.com>
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
abstract class MPEG_ABS_Object extends MPEG_Object
{
  /** @var integer */
  const VERSION_ONE = 3;
  
  /** @var integer */
  const VERSION_TWO = 2;
  
  /** @var integer */
  const VERSION_TWO_FIVE = 0;
  
  /** @var integer */
  const SAMPLING_FREQUENCY_LOW = 0;
  
  /** @var integer */
  const SAMPLING_FREQUENCY_HIGH = 1;
  
  /** @var integer */
  const LAYER_ONE = 3;
  
  /** @var integer */
  const LAYER_TWO = 2;

  /** @var integer */
  const LAYER_THREE = 1;
  
  /** @var integer */
  const CHANNEL_STEREO = 0;

  /** @var integer */
  const CHANNEL_JOINT_STEREO = 1;

  /** @var integer */
  const CHANNEL_DUAL_CHANNEL = 2;

  /** @var integer */
  const CHANNEL_SINGLE_CHANNEL = 3;

  /** @var integer */
  const MODE_SUBBAND_4_TO_31 = 0;

  /** @var integer */
  const MODE_SUBBAND_8_TO_31 = 1;

  /** @var integer */
  const MODE_SUBBAND_12_TO_31 = 2;

  /** @var integer */
  const MODE_SUBBAND_16_TO_31 = 3;

  /** @var integer */
  const MODE_ISOFF_MSSOFF = 0;

  /** @var integer */
  const MODE_ISON_MSSOFF = 1;

  /** @var integer */
  const MODE_ISOFF_MSSON = 2;

  /** @var integer */
  const MODE_ISON_MSSON = 3;

  /** @var integer */
  const EMPHASIS_NONE = 0;

  /** @var integer */
  const EMPHASIS_50_15 = 1;

  /** @var integer */
  const EMPHASIS_CCIT_J17 = 3;
  
  
  /**
   * Layer III side information size lookup table.  The table has the following
   * format.
   * 
   * <code>
   * array (
   *   SAMPLING_FREQUENCY_HIGH | SAMPLING_FREQUENCY_LOW => array (
   *     CHANNEL_STEREO | CHANNEL_JOINT_STEREO | CHANNEL_DUAL_CHANNEL |
   *       CHANNEL_SINGLE_CHANNEL => <size>
   *   )
   * )
   * </code>
   * 
   *
   * @var Array
   */
  protected static $sidesizes = array(
    self::SAMPLING_FREQUENCY_HIGH => array(
      self::CHANNEL_STEREO => 32,
      self::CHANNEL_JOINT_STEREO => 32,
      self::CHANNEL_DUAL_CHANNEL => 32,
      self::CHANNEL_SINGLE_CHANNEL => 17
    ),
    self::SAMPLING_FREQUENCY_LOW => array(
      self::CHANNEL_STEREO => 17,
      self::CHANNEL_JOINT_STEREO => 17,
      self::CHANNEL_DUAL_CHANNEL => 17,
      self::CHANNEL_SINGLE_CHANNEL => 9
    )
  );
  
  
  /**
   * Constructs the class with given parameters.
   *
   * @param Reader $reader The reader object.
   * @param Array $options The options array.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
  }
}
