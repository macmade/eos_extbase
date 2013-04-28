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
 * Localization utility
 *
 * @author      (c) 2009 - Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
class Tx_EosExtbase_Utility_Localization implements ArrayAccess
{
    protected $_extensionKey        = '';
    protected $_localizationUtility = NULL;
    protected $_labels              = array();
    
    public function __construct( $extensionKey )
    {
        $this->_extensionKey = ( string )$extensionKey;
        
        if( !t3lib_extMgm::isLoaded( $this->_extensionKey ) )
        {
            throw new Tx_EosExtbase_Utility_Localization_Exception
            (
                'The extension ' . $this->_extensionKey . ' is not loaded',
                Tx_EosExtbase_Utility_Localization_Exception::EXCEPTION_EXT_NOT_LOADED
            );
        }
        
        $this->_localizationUtility = new Tx_Extbase_Utility_Localization();
    }
    
    public function __call( $name, array $args = array() )
    {
        if( substr( $name, 0, 3 ) === 'get' )
        {
            $prop      = substr( $name, 3 );
            $prop[ 0 ] = strtolower( $prop[ 0 ] );
            
            return $this->$prop;
        }
        
        throw new Tx_EosExtbase_Utility_Localization_Exception
        (
            'Calling non existing method \'' . $name . '\' on object of type \'' . get_class( $this ) . '\'',
            Tx_EosExtbase_Utility_Localization_Exception::EXCEPTION_INVALID_METHOD_CALL
        );
    }
    
    public function __get( $name )
    {
        $name = ( string )$name;
        
        if( !isset( $this->_labels[ $name ] ) )
        {
            $label = $this->_localizationUtility->translate( $name, $this->_extensionKey );
            
            $this->_labels[ $name ] = $label;
        }
        
        if( $this->_labels[ $name ] === NULL )
        {
            return '[ L10N: ' . $name . ' ]';
        }
        
        return $this->_labels[ $name ];
    }
    
    public function __isset( $name )
    {
        $name  = ( string )$name;
        $label = $this->$name;
        
        return ( $this->_labels[ $name ] !== NULL );
    }
    
    public function offsetGet( $name )
    {
        $name = ( string )$name;
        
        return $this->$name;
    }
    
    public function offsetExists( $name )
    {
        $name = ( string )$name;
        
        return isset( $this->$name );
    }
    
    public function offsetSet( $name, $value )
    {}
    
    public function offsetUnset( $name )
    {}
}
