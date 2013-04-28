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
 * View helper for the forms
 * 
 * @author      Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  cp_guide
 */
class Tx_EosExtbase_ViewHelpers_FormViewHelper extends Tx_Fluid_ViewHelpers_FormViewHelper
{
    protected function setFormActionUri()
    {
        if( $this->arguments->hasArgument( 'actionUri' ) )
        {
            $formActionUri = $this->arguments[ 'actionUri' ];
        }
        else
        {
            $uriBuilder    = $this->controllerContext->getUriBuilder();
            
            $uriBuilder->reset();
            $uriBuilder->setTargetPageUid( $this->arguments[ 'pageUid' ] );
            $uriBuilder->setTargetPageType( $this->arguments[ 'pageType' ] );
            
            if( isset( $this->arguments[ 'noCache' ] ) && $this->arguments[ 'noCache' ] == 1 )
            {
                $uriBuilder->setNoCache( true );
            }
            
            if( isset( $this->arguments[ 'noCHash' ] ) && $this->arguments[ 'noCHash' ] == 1 )
            {
                $uriBuilder->setUseCacheHash( false );
            }
            
            $formActionUri = $uriBuilder->uriFor
            (
                $this->arguments[ 'action' ],
                $this->arguments[ 'arguments' ],
                $this->arguments[ 'controller' ],
                $this->arguments[ 'extensionName' ],
                $this->arguments[ 'pluginName' ]
            );
        }
        
        $this->tag->addAttribute( 'action', $formActionUri );
    }
    
    public function initializeArguments()
    {
        parent::initializeArguments();
        
        $this->registerArgument( 'noCache', 'boolean', '', false, $defaultValue = false );
        $this->registerArgument( 'noCHash', 'boolean', '', false, $defaultValue = false );
    }
}
