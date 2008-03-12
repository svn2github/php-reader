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

/**
 * An utility class to perform simple byte transformations on data.
 * 
 * @package   php-reader
 * @author    Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright Copyright (c) 2006, 2007 The Bearpaw Project Work Group
 * @copyright Copyright (c) 2007, 2008 BEHR Software Systems
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   $Rev$
 * @static
 */
final class Transform
{
  const MACHINE_ENDIAN_ORDER = 0;
  const LITTLE_ENDIAN_ORDER  = 1;
  const BIG_ENDIAN_ORDER     = 2;
  
  /**
   * Default private constructor for a static class.
   */
  private function __construct() {}
  
  /**
   * Returns machine-endian ordered binary data as 64-bit float. PHP does not
   * support 64-bit integers as the long integer is of 32-bits but using
   * aritmetic operations it is implicitly converted into floating point which
   * is of 64-bits long.
   *
   * @param  string  $raw   The raw data string.
   * @param  integer $order The byte order of the raw string.
   * @return integer
   */
  public static function getInt64($raw, $order = self::MACHINE_ENDIAN_ORDER)
  {
    list(, $lo, $hi) = unpack(($order == 2 ? "L" :
                               ($order == 1 ? "V" : "N")) . "*", $raw);
    return $hi * 0xffffffff + $lo;
  }

  /**
   * Returns little-endian ordered binary data as 64-bit float. PHP does not
   * support 64-bit integers as the long integer is of 32-bits but using
   * aritmetic operations it is implicitly converted into floating point which
   * is of 64-bits long.
   *
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getInt64LE($raw)
  {
    return self::getInt64($raw, self::LITTLE_ENDIAN_ORDER);
  }

  /**
   * Returns big-endian ordered binary data as 64-bit float. PHP does not
   * support 64-bit integers as the long integer is of 32-bits but using
   * aritmetic operations it is implicitly converted into floating point which
   * is of 64-bits long.
   *
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getInt64BE($raw)
  {
    return self::getInt64($raw, self::BIG_ENDIAN_ORDER);
  }

  /**
   * Returns machine-endian ordered binary data as signed 32-bit integer.
   *
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getInt32($raw)
  {
    list(, $int) = unpack("l*", $raw);
    return $int;
  }

  /**
   * Returns machine-endian ordered binary data as unsigned 32-bit integer.
   *
   * @param string  $raw   The raw data string.
   * @param integer $order The byte order of the raw string.
   * @return integer
   */
  public static function getUInt32($raw, $order = self::MACHINE_ENDIAN_ORDER)
  {
    list(, $int) = unpack(($order == 2 ? "N" :
                           ($order == 1 ? "V" : "L")) . "*", $raw);
    return $int;
  }

  /**
   * Returns little-endian ordered binary data as unsigned 32-bit integer.
   *
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getUInt32LE($raw)
  {
    return self::getUInt32($raw, self::LITTLE_ENDIAN_ORDER);
  }

  /**
   * Returns big-endian ordered binary data as unsigned 32-bit integer.
   *
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getUInt32BE($raw)
  {
    return self::getUInt32($raw, self::BIG_ENDIAN_ORDER);
  }

  /**
   * Returns machine endian ordered binary data as signed 16-bit integer.
   *
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getInt16($raw)
  {
    list(, $int) = unpack("s*", $raw);
    return $int;
  }

  /**
   * Returns machine endian ordered binary data as unsigned 16-bit integer.
   * 
   * @param string  $raw   The raw data string.
   * @param integer $order The byte order of the raw string.
   * @return integer
   */
  public static function getUInt16($raw, $order)
  {
    list(, $int) = unpack(($order == 2 ? "n" :
                           ($order == 1 ? "v" : "S")) . "*", $raw);
    return $int;
  }

  /**
   * Returns little-endian ordered binary data as unsigned 16-bit integer.
   * 
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getUInt16LE($raw)
  {
    return self::getUInt16($raw, self::LITTLE_ENDIAN_ORDER);
  }

  /**
   * Returns big-endian ordered binary data as unsigned 16-bit integer.
   * 
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getUInt16BE($raw)
  {
    return self::getUInt16($raw, self::BIG_ENDIAN_ORDER);
  }

  /**
   * Returns binary data as 8-bit integer.
   * 
   * @param string $raw The raw data string.
   * @return integer
   */
  public static function getInt8($raw)
  {
    return $raw;
  }

  /**
   * Returns binary data as string. Removes terminating zero.
   *
   * @param string $raw The raw data string.
   * @return string
   */
  public static function getString8($raw)
  {
    $string = "";
    foreach (unpack("C*", $raw) as $char)
      $string .= pack("c", $char);
    return rtrim($string, "\0");
  }

  /**
   * Returns machine-endian ordered binary data as multibyte string.
   *
   * @param string  $raw   The raw data string.
   * @param integer $order The byte order of the raw string.
   * @return string
   */
  public static function getString16($raw, $order = self::MACHINE_ENDIAN_ORDER)
  {
    $string = "";
    foreach (unpack(($order == 2 ? "n" :
                     ($order == 1 ? "v" : "S")) . "*", $raw) as $char)
      $string .= pack("S", $char);
    return $string;
  }

  /**
   * Returns little-endian ordered binary data as multibyte string.
   *
   * @param string $raw The raw data string.
   * @return string
   */
  public static function getString16LE($raw)
  {
    return self::getString16($raw, self::LITTLE_ENDIAN_ORDER);
  }

  /**
   * Returns big-endian ordered binary data as multibyte string.
   *
   * @param string $raw The raw data string.
   * @return string
   */
  public static function getString16BE($raw)
  {
    return self::getString16($raw, self::BIG_ENDIAN_ORDER);
  }

  /**
   * Returns binary data as hexadecimal string having high nibble first.
   * 
   * @param string $raw The raw data string.
   * @return string
   */
  public static function getHHex($raw)
  {
    list($hex) = unpack("H*0", $raw);
    return $hex; 
  }

  /**
   * Returns binary data as hexadecimal string having low nibble first.
   * 
   * @param string $raw The raw data string.
   * @return string
   */
  public static function getLHex($raw)
  {
    list($hex) = unpack("h*0", $raw);
    return $hex; 
  }

  /**
   * Returns the little-endian ordered raw data as big-endian ordered
   * hexadecimal GUID string.
   * 
   * @param string $raw The raw data string.
   * @return string
   */
  public static function getGUID($raw)
  {
    $C = @unpack("V1V/v2v/N2N", $raw);
    list($hex) = @unpack("H*0", pack
      ("NnnNN", $C["V"], $C["v1"], $C["v2"], $C["N1"], $C["N2"]));
    
    /* Fixes a bug in PHP versions earlier than Jan 25 2006 */
    if (implode("", unpack("H*", pack("H*", "a"))) == "a00")
      $hex = substr($hex, 0, -1);
      
    return preg_replace
      ("/^(.{8})(.{4})(.{4})(.{4})/", "\\1-\\2-\\3-\\4-", $hex);
  }
}
