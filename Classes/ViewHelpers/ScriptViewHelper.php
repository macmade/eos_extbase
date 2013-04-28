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
 * View helper for the scripts
 * 
 * @author      Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  cp_guide
 */
class Tx_EosExtbase_ViewHelpers_ScriptViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument( 'ext', 'string' );
        $this->registerArgument( 'script', 'string' );
        $this->registerArgument( 'type', 'string', '', false, $defaultValue = 'text/javascript' );
        $this->registerArgument( 'charset', 'string', '', false, $defaultValue = 'utf-8' );
    }
    
    public function render()
    {
        
        if( !t3lib_extMgm::isLoaded( $this->arguments[ 'ext' ] ) )
        {
            return;
        }
        
        $id   = md5( uniqid( microtime(), true ) );
        $path = t3lib_extMgm::siteRelPath( $this->arguments[ 'ext' ] )
              . 'Resources/Public/Scripts/' . $this->arguments[ 'script' ];
        
        if( TYPO3_MODE === 'FE' )
        {
            $GLOBALS[ 'TSFE' ]->additionalHeaderData[ $id ] = '<script type="'
                                                            . $this->arguments[ 'type' ]
                                                            . '" src="'
                                                            . $path
                                                            . '" charset="'
                                                            . $this->arguments[ 'charset' ]
                                                            . '"></script>';
        }
    }
}
