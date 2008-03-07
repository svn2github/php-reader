<?php
/**
 * $Id$
 *
 *
 * Copyright (C) 2008 BEHR Software Systems. All Rights Reserved.
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
 */

/**#@+ @ignore */
require_once("Reader.php");
/**#@-*/

/**
 * This class represents a file containing ID3v1 headers as described in
 * {@link http://www.id3.org/id3v2-00 The ID3-Tag Specification Appendix}.
 * 
 * @package   php-reader
 * @author    Sven Vollbehr <sven.vollbehr@behrss.eu>
 * @copyright 2008 BEHR Software Systems
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   $Rev$
 */
class ID3v1
{
  /** @var string The title */
  private $_title;
  
  /** @var string The artist */
  private $_artist;
  
  /** @var string The album */
  private $_album;
  
  /** @var string The year */
  private $_year;
  
  /** @var string The comment */
  private $_comment;
  
  /** @var integer The track number */
  private $_track;
  
  /** @var integer The genre index */
  private $_genre = 128;

  /** @var Array The genre list */
  public static $genres = array
    ("Blues", "Classic Rock", "Country", "Dance", "Disco", "Funk", "Grunge",
     "Hip-Hop", "Jazz", "Metal", "New Age", "Oldies", "Other", "Pop", "R&B",
     "Rap", "Reggae", "Rock", "Techno", "Industrial", "Alternative", "Ska",
     "Death Metal", "Pranks", "Soundtrack", "Euro-Techno", "Ambient",
     "Trip-Hop", "Vocal", "Jazz+Funk", "Fusion", "Trance", "Classical",
     "Instrumental", "Acid", "House", "Game", "Sound Clip", "Gospel", "Noise",
     "AlternRock", "Bass", "Soul", "Punk", "Space", "Meditative",
     "Instrumental Pop", "Instrumental Rock", "Ethnic", "Gothic", "Darkwave",
     "Techno-Industrial", "Electronic", "Pop-Folk", "Eurodance", "Dream",
     "Southern Rock", "Comedy", "Cult", "Gangsta", "Top ", "Christian Rap",
     "Pop/Funk", "Jungle", "Native American", "Cabaret", "New Wave",
     "Psychadelic", "Rave", "Showtunes", "Trailer", "Lo-Fi", "Tribal",
     "Acid Punk", "Acid Jazz", "Polka", "Retro", "Musical", "Rock & Roll",
     "Hard Rock", "Folk", "Folk-Rock", "National Folk", "Swing", "Fast Fusion",
     "Bebob", "Latin", "Revival", "Celtic", "Bluegrass", "Avantgarde",
     "Gothic Rock", "Progressive Rock", "Psychedelic Rock", "Symphonic Rock",
     "Slow Rock", "Big Band", "Chorus", "Easy Listening", "Acoustic", "Humour",
     "Speech", "Chanson", "Opera", "Chamber Music", "Sonata", "Symphony",
     "Booty Bass", "Primus", "Porn Groove", "Satire", "Slow Jam", "Club",
     "Tango", "Samba", "Folklore", "Ballad", "Power Ballad", "Rhythmic Soul",
     "Freestyle", "Duet", "Punk Rock", "Drum Solo", "A capella", "Euro-House",
     "Dance Hall", "Unknown");
  
  /** @var Reader The Reader object */
  private $_reader;
  
  /**
   * The default constructor. Initiates the reader for the given file.
   */
  public function __construct($filename)
  {
    $this->_reader = new Reader($filename);
    $this->_reader->setOffset(-128);
    if ($this->_reader->getString8(3) != "TAG")
      throw new ID3_Exception("File does not contain ID3v1 tag: " . $filename);
    
    $this->_title = rtrim($this->_reader->getString8(30), " \0");
    $this->_artist = rtrim($this->_reader->getString8(30), " \0");
    $this->_album = rtrim($this->_reader->getString8(30), " \0");
    $this->_year = $this->_reader->getString8(4);
    $this->_comment = rtrim($this->_reader->getString8(28), " \0");

    /* ID3v1.1 support for tracks */
    $v11_null = $this->_reader->getInt8();
    $v11_track = $this->_reader->getInt8();
    if (ord($v11_null) == 0 && ord($v11_track) != 0)
      $this->_track = ord($v11_track);
    else
      $this->_comment = rtrim($this->_comment . $v11_null . $v11_track, " \0");
    
    $this->_genre = ord($this->_reader->getInt8());
  }
  
  /**
   * Returns the title field.
   *
   * @return string Returns the title field.
   */
  public function getTitle() { return $this->_title; }
  
  /**
   * Returns the artist field.
   *
   * @return string Returns the artist field.
   */
  public function getArtist() { return $this->_artist; }
  
  /**
   * Returns the album field.
   *
   * @return string Returns the album field.
   */
  public function getAlbum() { return $this->_album; }
  
  /**
   * Returns the year field.
   *
   * @return string Returns the year field.
   */
  public function getYear() { return $this->_year; }
  
  /**
   * Returns the comment field.
   *
   * @return string Returns the comment field.
   */
  public function getComment() { return $this->_comment; }
  
  /**
   * Returns the track field.
   *
   * @note ID3v1.1 feature only
   * @return integer Returns the track field.
   */
  public function getTrack() { return $this->_track; }
  
  /**
   * Returns the genre.
   *
   * @return string Returns the genre.
   */
  public function getGenre() {
    if (isset(self::$genres[$this->_genre]))
      return self::$genres[$this->_genre];
    else
      return self::$genres[128]; // unknown
  }
  
  /**
   * Magic function so that $obj->value will work.
   *
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    if (method_exists($this, "get" . ucfirst(strtolower($name))))
      return call_user_func(array($this, "get" . ucfirst(strtolower($name))));
  }
}
