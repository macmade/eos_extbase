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
 * Repository
 * 
 * @author      Jean-David Gadina <macmade@eosgarden.com>
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
abstract class Tx_EosExtbase_Persistence_Repository extends Tx_Extbase_Persistence_Repository
{
    protected function _getOrderingField()
    {
        $tableParts      = explode( '_', substr( strtolower( get_class( $this ) ), 0, -10 ) );
        $tableParts[ 3 ] = 'model';
        $table           = implode( '_', $tableParts );
        
        if( isset( $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ] ) )
        {
            $ctrl =& $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ];
            
            if( isset( $ctrl[ 'sortby' ] ) )
            {
                return $ctrl[ 'sortby' ];
            }
            
            if( isset( $ctrl[ 'default_sortby' ] ) )
            {
                return $ctrl[ 'default_sortby' ];
            }
            
            if( isset( $ctrl[ 'label' ] ) )
            {
                return $ctrl[ 'label' ];
            }
        }
        
        return 'uid';
    }
    
    public function findByUid( $uid, $checkStoragePage = true )
    {
        if( ( boolean )$checkStoragePage === true )
        {
            return parent::findByUid( $uid );
        }
        
        if( !is_int( $uid ) || $uid < 0 )
        {
            throw new InvalidArgumentException
            (
                'The uid must be a positive integer',
                1245071889
            );
        }
        
        if( $this->identityMap->hasIdentifier( $uid, $this->objectType ) )
        {
            $object = $this->identityMap->getObjectByIdentifier( $uid, $this->objectType );
        }
        else
        {
            $query = $this->createQuery();
            
            $query->getQuerySettings()->setRespectSysLanguage( false );
            $query->getQuerySettings()->setRespectStoragePage( false );
            
            $result = $query->matching( $query->withUid( $uid ) )->execute();
            
            $object = NULL;
            
            if( count( $result ) > 0 )
            {
                $object = current( $result );
                
                $this->identityMap->registerObject( $object, $uid );
            }
        }
        
        return $object;
    }
    
    public function findAllByPid( $pid )
    {
        $pid   = ( int )$pid;
        $query = $this->createQuery();
        
        $query->getQuerySettings()->setRespectStoragePage( false );
        $query->matching( $query->in( 'pid', array( $pid ) ) );
        $query->setOrderings( array( $this->_getOrderingField() => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING ) );
        
        return $query->execute();
    }
    
    public function findAll()
    {
        $query = $this->createQuery();
        
        $query->setOrderings( array( $this->_getOrderingField() => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING ) );
        
        return $query->execute();
    }
}
