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
 * Abstract entity
 * 
 * @author      Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
abstract class Tx_EosExtbase_DomainObject_AbstractEntity extends Tx_Extbase_DomainObject_AbstractEntity
{
    const PROPERTY_TYPE_UNDEFINED = 0x00;
    const PROPERTY_TYPE_ARRAY     = 0x01;
    const PROPERTY_TYPE_BOOL      = 0x02;
    const PROPERTY_TYPE_DOUBLE    = 0x03;
    const PROPERTY_TYPE_FLOAT     = 0x04;
    const PROPERTY_TYPE_INT       = 0x05;
    const PROPERTY_TYPE_STRING    = 0x06;
    
    private $_classProperties    = array();
    private $_hasClassProperties = false;
    private $_id                 = 0;
    
    /**
     * PID
     * 
     * @var         int
     */
    protected $pid      = 0;
    
    /**
     * Cration date
     * 
     * @var         int
     */
    protected $crdate   = 0;
    
    /**
     * Modification date
     * 
     * @var         int
     */
    protected $tstamp   = 0;
    
    public function __construct()
    {
        $this->crdate = $this->tstamp = time();
    }
    
    private function _setClassProperty( $prop, $value )
    {
        $tags = $this->_classProperties[ $prop ][ 'tags' ];
        
        if( isset( $tags[ 'var' ] ) )
        {
            if
            (
                !isset( $tags[ 'lazy' ] )
              && isset( $tags[ 'var' ][ 0 ] )
              && substr( $tags[ 'var' ][ 0 ], 0, 36 ) === 'Tx_Extbase_Persistence_ObjectStorage'
            )
            {
                $this->$prop = clone( $value );
                
                return;
            }
            
            switch( $tags[ 'var' ] )
            {
                case 'int':
                case 'integer':
                    
                    $this->$prop = ( int )$value;
                    return;
                
                case 'string':
                    
                    $this->$prop = ( string )$value;
                    return;
                
                case 'float':
                    
                    $this->$prop = ( float )$value;
                    return;
                
                case 'double':
                    
                    $this->$prop = ( double )$value;
                    return;
                
                case 'array':
                    
                    $this->$prop = ( array )$value;
                    return;
                
                case 'bool':
                    
                    $this->$prop = ( bool )$value;
                    return;
            }
        }
        
        switch( $this->_classProperties[ $prop ][ 'type' ] )
        {
                case self::PROPERTY_TYPE_INT:
                    
                    $this->$prop = ( int )$value;
                    return;
                
                case self::PROPERTY_TYPE_STRING:
                    
                    $this->$prop = ( string )$value;
                    return;
                
                case self::PROPERTY_TYPE_FLOAT:
                    
                    $this->$prop = ( float )$value;
                    return;
                
                case self::PROPERTY_TYPE_DOUBLE:
                    
                    $this->$prop = ( double )$value;
                    return;
                
                case self::PROPERTY_TYPE_ARRAY:
                    
                    $this->$prop = ( array )$value;
                    return;
                
                case self::PROPERTY_TYPE_BOOL:
                    
                    $this->$prop = ( bool )$value;
                    return;
                
                default:
                    
                    $this->$prop = $value;
                    return;
        }
    }
    
    private function _getClassProperty( $prop )
    {
        $tags = $this->_classProperties[ $prop ][ 'tags' ];
        
        if
        (
               is_object( $this->$prop )
            && isset( $tags[ 'var' ] )
            && isset( $tags[ 'lazy' ] )
        )
        {
            if( $this->$prop instanceof Tx_Extbase_Persistence_LazyLoadingProxy )
            {
                $this->$prop->_loadRealInstance();
            }
        }
        
        return $this->$prop;
    }
    
    public function __call( $name, array $args = array() )
    {
        if( $this->_hasClassProperties === false )
        {
            $this->initializeObject();
        }
        
        if( substr( $name, 0, 3 ) === 'set' && isset( $args[ 0 ] ) )
        {
            $prop      = substr( $name, 3 );
            $prop[ 0 ] = strtolower( $prop[ 0 ] );
            
            if( isset( $this->_classProperties[ $prop ] ) )
            {
                $this->_setClassProperty( $prop, $args[ 0 ] );
                
                return;
            }
        }
        elseif( substr( $name, 0, 3 ) === 'get' )
        {
            $prop      = substr( $name, 3 );
            $prop[ 0 ] = strtolower( $prop[ 0 ] );
            
            if( isset( $this->_classProperties[ $prop ] ) )
            {
                return $this->_getClassProperty( $prop );
            }
        }
        
        throw new Tx_EosExtbase_DomainObject_AbstractEntity_Exception
        (
            'Calling non existing method \'' . $name . '\' on object of type \'' . get_class( $this ) . '\'',
            Tx_EosExtbase_DomainObject_AbstractEntity_Exception::EXCEPTION_INVALID_METHOD_CALL
        );
    }
    
    public function initializeObject()
    {
        if( $this->_hasClassProperties === true )
        {
            return;
        }
        
        $className = get_class( $this );
        $class     = new Tx_Extbase_Reflection_ClassReflection( $className );
        $props     = $class->getProperties( ReflectionProperty::IS_PROTECTED );
        
        foreach( $props as $prop )
        {
            if( substr( $prop->name, 0, 1 ) === '_' || $prop->name === 'uid' )
            {
                continue;
            }
            
            $propName = $prop->name;
            
            if( is_array( $this->$propName ) )
            {
                $type = self::PROPERTY_TYPE_ARRAY;
            }
            elseif( is_bool( $this->$propName ) )
            {
                $type = self::PROPERTY_TYPE_BOOL;
            }
            elseif( is_double( $this->$propName ) )
            {
                $type = self::PROPERTY_TYPE_DOUBLE;
            }
            elseif( is_float( $this->$propName ) )
            {
                $type = self::PROPERTY_TYPE_FLOAT;
            }
            elseif( is_int( $this->$propName ) )
            {
                $type = self::PROPERTY_TYPE_INT;
            }
            elseif( is_string( $this->$propName ) )
            {
                $type = self::PROPERTY_TYPE_STRING;
            }
            else
            {
                $type = self::PROPERTY_TYPE_UNDEFINED;
            }
            
            $this->_classProperties[ $prop->name ] = array(
                'tags' => $prop->getTagsValues(),
                'type' => $type
            );
        }
    }
    
    public function getCTime()
    {
        return new DateTime( date( 'c', $this->crdate ) );
    }
    
    public function getMTime()
    {
        return new DateTime( date( 'c', $this->tstamp ) );
    }
}
