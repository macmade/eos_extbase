<?php
################################################################################
#                                                                              #
#                               COPYRIGHT NOTICE                               #
#                                                                              #
# (c) 2009 eosgarden                                                           #
# All rights reserved                                                          #
#                                                                              #
# This script is part of the TYPO3 project. The TYPO3 project is free          #
# software. You can redistribute it and/or modify it under the terms of the    #
# GNU General Public License as published by the Free Software Foundation,     #
# either version 2 of the License, or (at your option) any later version.      #
#                                                                              #
# The GNU General Public License can be found at:                              #
# http://www.gnu.org/copyleft/gpl.html.                                        #
#                                                                              #
# This script is distributed in the hope that it will be useful, but WITHOUT   #
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or        #
# FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for    #
# more details.                                                                #
#                                                                              #
# This copyright notice MUST APPEAR in all copies of the script!               #
################################################################################

# $Id$

// DEBUG ONLY - Sets the error reporting level to the highest possible value
#error_reporting( E_ALL | E_STRICT );

/**
 * TYPO3 class manager
 *
 * @author      Jean-David Gadina <macmade@eosgarden.com>
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
final class Tx_EosExtbase_Typo3_ClassManager
{
    /**
     * The unique instance of the class (singleton)
     */
    private static $_instance = NULL;
    
    /**
     * The loaded classes from this TYPO3
     */
    private $_loadedClasses   = array();
    
    /**
     * The path from which to load classes
     */
    private $_classDirs       = array();
    
    /**
     * Class constructor
     * 
     * The class constructor is private to avoid multiple instances of the
     * class (singleton).
     * 
     * @return NULL
     */
    private function __construct()
    {
        // Stores the paths to the T3Lib and TSLib classes
        $this->_classDirs[ 't3lib' ] = PATH_t3lib;
        $this->_classDirs[ 'tslib' ] = t3lib_extMgm::extPath( 'cms' ) . 'tslib' . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Clones an instance of the class
     * 
     * A call to this method will produce an exception, as the class cannot
     * be cloned (singleton).
     * 
     * @return  NULL
     * @throws  Tx_EosExtbase_Core_Singleton_Exception  Always, as the class cannot be cloned (singleton)
     */
    public function __clone()
    {
        throw new Tx_EosExtbase_Core_Singleton_Exception(
            'Class ' . __CLASS__ . ' cannot be cloned',
            Tx_EosExtbase_Core_Singleton_Exception::EXCEPTION_CLONE
        );
    }
    
    /**
     * Serializes an instance of the class
     * 
     * A call to this method will produce an exception, as the class cannot
     * be serialized (singleton).
     * 
     * @return  NULL
     * @throws  Tx_EosExtbase_Core_Singleton_Exception  Always, as the class cannot be serialized (singleton)
     */
    public function __sleep()
    {
        throw new Tx_EosExtbase_Core_Singleton_Exception(
            'Class ' . __CLASS__ . ' cannot be serialized',
            Tx_EosExtbase_Core_Singleton_Exception::EXCEPTION_SLEEP
        );
    }
    
    /**
     * Unserializes an instance of the class
     * 
     * A call to this method will produce an exception, as the class cannot
     * be unserialized (singleton).
     * 
     * @return  NULL
     * @throws  Tx_EosExtbase_Core_Singleton_Exception  Always, as the class cannot be unserialized (singleton)
     */
    public function __wakeup()
    {
        throw new Tx_EosExtbase_Core_Singleton_Exception(
            'Class ' . __CLASS__ . ' cannot be unserialized',
            Tx_EosExtbase_Core_Singleton_Exception::EXCEPTION_WAKEUP
        );
    }
    
    /**
     * Gets the unique class instance
     * 
     * This method is used to get the unique instance of the class
     * (singleton). If no instance is available, it will create it.
     * 
     * @return  Tx_EosExtbase_Typo3_ClassManager    The unique instance of the class
     */
    public static function getInstance()
    {
        // Checks if the unique instance already exists
        if( !is_object( self::$_instance ) )
        {
            // Creates the unique instance
            self::$_instance = new self();
        }
        
        // Returns the unique instance
        return self::$_instance;
    }
    
    /**
     * SPL autoload method
     * 
     * When registered with the spl_autoload_register() function, this method
     * will be called each time a class cannot be found, and will try to
     * load it.
     * 
     * @param   string  The name of the class to load
     * @return  boolean
     * @see     getInstance
     * @see     _loadClass
     */
    public static function autoLoad( $className )
    {
        // Instance of this class
        static $instance = NULL;
        
        // Checks if the instance of the class has already been fetched
        if( !is_object( $instance ) )
        {
            // Gets the instance of this class
            $instance = self::getInstance();
        }
        
        // Gets the class prefix
        $prefix = substr( $className, 0, strpos( $className, '_' ) );
        
        // Checks if the prefix corresponds to a TYPO3 package
        if( isset( $instance->_classDirs[ $prefix ] ) )
        {
            // Loads the class
            $instance->_loadClass( $className, $prefix );
        }
        
        // The requested class does not belong to a registered prefix
        return false;
    }
    
    /**
     * Loads a class from a TYPO3 directory
     * 
     * @param   string  The name of the class to load
     * @param   string  The class prefix
     * @return  boolean
     */
    private function _loadClass( $className, $prefix )
    {
        // Gets the class path
        $classPath = $this->_classDirs[ $prefix ] . 'class.' . strtolower( $className ) . '.php';
        
        // Checks if the class file exists
        if( file_exists( $classPath ) )
        {
            // Adds the class to the loaded classes array
            $this->_loadedClasses[ $className ] = $classPath;
            
            // Includes the class file
            require_once( $classPath );
            return true;
        }
        
        // Class file was not found
        return false;
    }
    
    /**
     * Gets the loaded classes
     * 
     * @return  array   An array with the loaded classes
     */
    public function getLoadedClasses()
    {
        // Returns the loaded classes
        return $this->_loadedClasses;
    }
}
