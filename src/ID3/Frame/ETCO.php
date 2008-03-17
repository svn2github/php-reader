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
 * @package    php-reader
 * @subpackage ID3
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ID3/Frame.php");
require_once("ID3/Timing.php");
/**#@-*/

/**
 * The <i>Event timing codes</i> allows synchronisation with key events in the
 * audio.
 *
 * The events are an array of timestamp and type pairs. The time stamp is set to
 * zero if directly at the beginning of the sound or after the previous event.
 * All events are sorted in chronological order.
 *
 * The events $E0-EF are for user events. You might want to synchronise your
 * music to something, like setting off an explosion on-stage, activating a
 * screensaver etc.
 *
 * There may only be one ETCO frame in each tag.
 *
 * @package    php-reader
 * @subpackage ID3
 * @author     Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright  Copyright (c) 2008 BEHR Software Systems
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Rev$
 */
final class ID3_Frame_ETCO extends ID3_Frame
  implements ID3_Timing
{
  /**
   * The list of event types.
   *
   * @var Array
   */
  public static $types = array
    ("Padding", "End of initial silence", "Intro start", "Main part start",
     "Outro start", "Outro end", "Verse start","Refrain start",
     "Interlude start", "Theme start", "Variation start", "Key change",
     "Time change", "Momentary unwanted noise", "Sustained noise",
     "Sustained noise end", "Intro end", "Main part end", "Verse end",
     "Refrain end", "Theme end", "Profanity", "Profanity end",
    
     0xe0 => "User event", "User event", "User event", "User event",
     "User event", "User event", "User event", "User event", "User event",
     "User event", "User event", "User event", "User event", "User event",
    
     0xfd => "Audio end (start of silence)", "Audio file ends",
     "One more byte of events follows");
  
  /** @var integer */
  private $_format;
  
  /** @var Array */
  private $_events = array();
  
  /**
   * Constructs the class with given parameters and parses object related data.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader)
  {
    parent::__construct($reader);

    $this->_format = ord($this->_data{0});
    
    for ($i = 1; $i < $this->getSize(); $i += 5) {
      $this->_events[Transform::getInt32BE(substr($this->_data, $i + 1, 4))] =
        $data = $this->_data{$i};
      if ($data == 0xff)
        break;
    }
    sort($this->_events);
  }
  
  /**
   * Returns the timing format.
   * 
   * @return integer
   */
  public function getFormat() { return $this->_format; }

  /**
   * Returns the events as an associated array having the timestamps as keys and
   * the event types as values.
   * 
   * @return string
   */
  public function getEvents() { return $this->_events; }
}
