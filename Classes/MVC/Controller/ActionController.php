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
 * A multi action controller
 * 
 * @author      (c) 2009 - Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 * @default
 */
class Tx_EosExtbase_MVC_Controller_ActionController extends Tx_Extbase_MVC_Controller_ActionController
{
    protected $_cObj = NULL;
    protected $_lang = NULL;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_cObj = t3lib_div::makeInstance( 'tslib_cObj' );
        $this->_lang = new Tx_EosExtbase_Utility_Localization
        (
            t3lib_div::camelCaseToLowerCaseUnderscored( $this->extensionName )
        );
    }
    
    public function initializeView()
    {
        $this->view->assign( 'l10n', $this->_lang );
    }
    
    protected function _clearPageCache( $pid = 0 )
    {
        $pid = ( int )$pid;
        
        if( $pid === 0 && defined( 'TYPO3_MODE' ) && TYPO3_MODE == 'FE' )
        {
            $pid = $GLOBALS[ 'TSFE' ]->id;
        }
        
        if( $pid !== 0 )
        {
            $GLOBALS[ 'TYPO3_DB' ]->exec_DELETEquery( 'cache_pages', 'page_id=' . $pid );
            $GLOBALS[ 'TYPO3_DB' ]->exec_DELETEquery( 'cache_pagesection', 'page_id=' . $pid );
        }
    }
    
    protected function _frontendUserLogin( Tx_EosExtbase_Domain_Model_FrontendUser $user )
    {
        $_POST[ 'logintype' ] = 'login';
        $_POST[ 'user' ]      = $user->getUsername();
        $_POST[ 'pass' ]      = $user->getPassword();
        $_POST[ 'pid' ]       = $user->getPid();
        
        $GLOBALS[ 'TSFE' ]->initFEuser();
        
        unset( $_POST[ 'logintype' ] );
        unset( $_POST[ 'user' ] );
        unset( $_POST[ 'pass' ] );
        unset( $_POST[ 'pid' ] );
    }
    
    protected function _setPageTitle( $title, $replace = false, $separator = ' - ' )
    {
        $title     = ( string )$title;
        $separator = ( string )$separator;
        
        if( ( boolean )$replace == true )
        {
            $GLOBALS[ 'TSFE' ]->page[ 'title' ] = $title;
            $GLOBALS[ 'TSFE' ]->indexedDocTitle = $title;
        }
        else
        {
            $GLOBALS[ 'TSFE' ]->page[ 'title' ] .= $separator . $title;
            $GLOBALS[ 'TSFE' ]->indexedDocTitle .= $separator . $title;
        }
        
        if( isset( $this->_plugin->conf[ 'titleAddText' ] ) && $this->_plugin->conf[ 'titleAddText' ] )
        {
            $GLOBALS[ 'TSFE' ]->page[ 'title' ] .= ' ' . $this->_plugin->conf[ 'titleAddText' ];
        }
    }
    
    protected function _getLink( $action, array $params = array(), $controller = '', $extension = '', $pid = 0 )
    {
        $pid         = ( !$pid && defined( 'TYPO3_MODE' ) && TYPO3_MODE == 'FE' ) ? $GLOBALS[ 'TSFE' ]->id : ( int )$pid;
        $class       = get_class( $this );
        $controller  = ( $controller ) ? ( string )$controller : substr( $class, strrpos( $class, '_' ) + 1, -10 );
        $ext         = ( $ext ) ? ( string )$ext : substr( $class, 3, strpos( $class, '_', 3 ) - 3 );
        $plugin      = 'tx_' . strtolower( str_replace( '_', '', $ext ) ) . '_pi1';
        $queryString = '&' . $plugin . '[action]=' . $action . '&' . $plugin . '[controller]=' . $controller;
        
        foreach( $params as $key => $value )
        {
            $queryString .= '&' . $plugin . '[' . $key . ']=' . ( string )$value;
        }
        
        $href = $this->_cObj->typoLink_URL
        (
            array
            (
                'parameter'        => $pid,
                'additionalParams' => $queryString
            )
        );
        
        return t3lib_div::getIndpEnv( 'TYPO3_SITE_URL' ) . $href;
    }
}
