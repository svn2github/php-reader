<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Media
 * @subpackage Vorbis
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once 'Zend/Io/Reader.php';
/**#@-*/

/**
 * A Vorbis bitstream begins with three header packets. The header packets are, in order, the identication header, the
 * comments header, and the setup header. All are required for decode compliance. This class is the base class for all
 * these headers.
 *
 * @category   Zend
 * @package    Zend_Media
 * @subpackage Vorbis
 * @author     Sven Vollbehr <sven@vollbehr.eu>
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
abstract class Zend_Media_Vorbis_Header
{
    /**
     * The reader object.
     *
     * @var Reader
     */
    protected $_reader;

    /**
     * The options array.
     *
     * @var Array
     */
    private $_options;

    /**
     * The packet type; the identication header is type 1, the comment header type 3 and the setup header type 5.
     *
     * @var Array
     */
    protected $_packetType;

    /**
     * Constructs the class with given parameters.
     *
     * @param Zend_Io_Reader $reader The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = array())
    {
        $this->_reader = $reader;
        $this->_options = &$options;

        if (!in_array($this->_packetType = $this->_reader->readUInt8(), array(1, 3, 5))) {
            require_once 'Zend/Media/Vorbis/Exception.php';
            throw new Zend_Media_Vorbis_Exception('Unknown header packet type: ' . $this->_packetType);
        }
        if (($vorbis = $this->_reader->read(6)) != 'vorbis') {
            require_once 'Zend/Media/Vorbis/Exception.php';
            throw new Zend_Media_Vorbis_Exception('Unknown header packet: ' . $vorbis);
        }
    }

    /**
     * Returns the options array.
     *
     * @return Array
     */
    public final function &getOptions()
    {
        return $this->_options;
    }

    /**
     * Returns the given option value, or the default value if the option is not
     * defined.
     *
     * @param string $option The name of the option.
     * @param mixed $defaultValue The default value to be returned.
     */
    public final function getOption($option, $defaultValue = null)
    {
        if (isset($this->_options[$option])) {
            return $this->_options[$option];
        }
        return $defaultValue;
    }

    /**
     * Sets the options array. See main class for available options.
     *
     * @param Array $options The options array.
     */
    public final function setOptions(&$options)
    {
        $this->_options = &$options;
    }

    /**
     * Sets the given option the given value.
     *
     * @param string $option The name of the option.
     * @param mixed $value The value to set for the option.
     */
    public final function setOption($option, $value)
    {
        $this->_options[$option] = $value;
    }

    /**
     * Clears the given option value.
     *
     * @param string $option The name of the option.
     */
    public final function clearOption($option)
    {
        unset($this->_options[$option]);
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $name The field name.
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func(array($this, 'get' . ucfirst($name)));
        } else {
            require_once 'Zend/Media/Vorbis/Exception.php';
            throw new Zend_Media_Vorbis_Exception('Unknown field: ' . $name);
        }
    }

    /**
     * Magic function so that assignments with $obj->value will work.
     *
     * @param string $name  The field name.
     * @param string $value The field value.
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set' . ucfirst($name))) {
            call_user_func
                (array($this, 'set' . ucfirst($name)), $value);
        } else {
            require_once 'Zend/Media/Vorbis/Exception.php';
            throw new Zend_Media_Vorbis_Exception('Unknown field: ' . $name);
        }
    }
}
