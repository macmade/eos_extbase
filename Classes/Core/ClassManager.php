<?php
################################################################################
#                                                                              #
#                               COPYRIGHT NOTICE                               #
#                                                                              #
# (c) 2009 XS-Labs                                                             #
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
 * Class manager
 *
 * @author      Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
final class Tx_EosExtbase_Core_ClassManager
{
    /**
     * The unique instance of the class (singleton)
     */
    private static $_instance = NULL;
    
    /**
     * The loaded classes
     */
    private $_loadedClasses   = array();
    
    /**
     * Class constructor
     * 
     * The class constructor is private to avoid multiple instances of the
     * class (singleton).
     * 
     * @return NULL
     */
    private function __construct()
    {}
    
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
        throw new Tx_EosExtbase_Core_Singleton_Exception
        (
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
        throw new Tx_EosExtbase_Core_Singleton_Exception
        (
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
        throw new Tx_EosExtbase_Core_Singleton_Exception
        (
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
     * @return  Tx_EosExtbase_Core_ClassManager     The unique instance of the class
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
        
        // Checks if the class belongs to TYPO3
        if( substr( $className, 0, 3 ) === 'Tx_' )
        {
            // Tries to loads the class
            return $instance->_loadClass( $className );
        }
        
        // The requested class does not belong to this project
        return false;
    }
    
    /**
     * Loads a class from a TYPO3 extension
     * 
     * @param   string  The name of the class to load
     * @return  boolean
     */
    private function _loadClass( $className )
    {
        // Gets the extension key part
        $ext       = substr( $className, 3, strpos( $className, '_', 3 ) - 3 );
        $extLength = strlen( $ext );
        $extKey    = '';
        
        // Process each character of the class name
        for( $i = 0; $i < $extLength; $i++ )
        {
            // Gets the ASCII value of the current character
            $char = ord( $ext[ $i ] );
            
            // Checks the character value
            if( $i === 0 )
            {
                // First character - Converts to lowercase
                $extKey .= chr( $char + 32 );
            }
            elseif( $char > 64 && $char < 91 )
            {
                // Uppercase - Places an underscore
                $extKey .= '_' . chr( $char + 32 );
            }
            else
            {
                // Lowercase character
                $extKey .= $ext[ $i ];
            }
        }
        
        // Checks if the extension is loaded
        if( t3lib_extMgm::isLoaded( $extKey ) === false )
        {
            // Extension not loaded
            return false;
        }
        
        // Gets the path to the class file
        $classDir  = t3lib_extMgm::extPath( $extKey ) . 'Classes' . DIRECTORY_SEPARATOR;
        $classFile = str_replace( '_', DIRECTORY_SEPARATOR, substr( $className, 3 + $extLength + 1 ) );
        $classPath = $classDir . $classFile . '.php';
        
        // Checks if the class file exists
        if( file_exists( $classPath ) )
        {
            // Stores the class path
            $this->_loadedClasses[ $className ] = $classPath;
            
            // Loads the class file
            require_once( $classPath );
            
            if( !class_exists( $className ) )
            {
                return false;
            }
            
            if( method_exists( $className, 'initStatic' ) )
            {
                call_user_method( 'initStatic', $className );
            }
            
            return true;
        }
        
        // Class file was not found
        return false;
    }
    
    /**
     * Gets the loaded classes from this project
     * 
     * @return  array   An array with the loaded classes
     */
    public function getLoadedClasses()
    {
        // Returns the loaded classes from this project
        return $this->_loadedClasses;
    }
}
