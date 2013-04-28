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
 * A general purpose configuration manager used in frontend mode
 *
 * @author      (c) 2009 - Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
class Tx_EosExtbase_Configuration_FrontendConfigurationManager extends Tx_Extbase_Configuration_FrontendConfigurationManager
{
    protected function overrideSwitchableControllerActionsFromFlexform( $frameworkConfiguration, $flexformConfiguration )
    {
        $extensionName = $frameworkConfiguration[ 'extensionName' ];
        $extensionKey  = t3lib_div::camelCaseToLowerCaseUnderscored( $extensionName );
        
        if( isset( $flexformConfiguration[ 'availableControllers' ] ) && !isset( $flexformConfiguration[ 'switchableControllerActions' ] ) )
        {
            $switchableControllerActions = array();
            $controllers                 = explode( ';', $flexformConfiguration[ 'availableControllers' ] );
            $controllersAndActions       = Tx_EosExtbase_Utility_Extension::getControllersAndActions( $extensionKey );
            
            foreach( $controllers as $controller )
            {
                $controller = trim( $controller );
                
                if( !isset( $controllersAndActions[ 0 ][ $controller ] ) )
                {
                    continue;
                }
                
                foreach( $controllersAndActions[ 0 ][ $controller ] as $action )
                {
                    $switchableControllerActions[] = $controller . '->' . $action;
                }
            }
            
            $flexformConfiguration[ 'switchableControllerActions' ] = implode( ';', $switchableControllerActions );
            
            unset( $flexformConfiguration[ 'availableControllers' ] );
        }
        
        return parent::overrideSwitchableControllerActionsFromFlexform( $frameworkConfiguration, $flexformConfiguration );
    }
    
    protected function getContextSpecificFrameworkConfiguration( $frameworkConfiguration )
    {
        $conf = parent::getContextSpecificFrameworkConfiguration( $frameworkConfiguration );
        
        if( isset( $conf[ 'persistence' ][ 'recursiveStoragePid' ] ) && $conf[ 'persistence' ][ 'recursiveStoragePid' ] == 1 )
        {
            $pages = t3lib_div::trimExplode( ',', $conf[ 'persistence' ][ 'storagePid' ] );
            
            foreach( $pages as $pid )
            {
                $subPages = $this->_getSubPages( $pid );
                
                if( count( $subPages ) )
                {
                    $conf[ 'persistence' ][ 'storagePid' ] .= ',' . implode( ',', $subPages );
                }
            }
        }
        
        return $conf;
    }
    
    protected function _getSubPages( $pid )
    {
        $pidList = array();
        $res     = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery
        (
            'uid,deleted,hidden',
            'pages',
            'pid = ' . $pid
        );
        
        if( $res && $GLOBALS[ 'TYPO3_DB' ]->sql_num_rows( $res ) )
        {
            while( $row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res ) )
            {
                $pidList[] = $row[ 'uid' ];
                $pidList   = array_merge( $pidList, $this->_getSubPages( $row[ 'uid' ] ) );
            }
        }
        
        return $pidList;
    }
}
