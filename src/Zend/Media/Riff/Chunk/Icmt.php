<?php
/**
 * @category   Zend
 * @package    Zend_Media
 * @subpackage Riff
 * @copyright  Copyright (c) 2011 Sven Vollbehr
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once 'Zend/Media/Riff/StringChunk.php';
/**#@-*/

/**
 * The <i>Comments</i> chunk provides general comments about the file or the subject of the file. If the comment is
 * several sentences long, end each sentence with a period. Do not include newline characters.
 *
 * @category   Zend
 * @package    Zend_Media
 * @subpackage Riff
 * @author     Sven Vollbehr <sven@vollbehr.eu>
 * @copyright  Copyright (c) 2011 Sven Vollbehr
 * @license    http://framework.zend.com/license/new-bsd New BSD License
 * @version    $Id$
 */
final class Zend_Media_Riff_Chunk_Icmt extends Zend_Media_Riff_StringChunk
{
}
