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
 * View helper for an image object
 * 
 * @author      Jean-David Gadina <macmade@eosgarden.com>
 * @version     1.0
 * @package     TYPO3
 * @subpackage  cp_guide
 */
class Tx_EosExtbase_ViewHelpers_Element_AlternateClassViewHelper extends Tx_Fluid_Core_ViewHelper_TagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'div';
    
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
    }
    
    /**
     * @param   string  $tag
     * @param   string  $even
     * @param   string  $odd
     * @param   string  $group
     */
    public function render( $tag, $even, $odd, $group = '' )
    {
        static $groups = array();
        
        if( !isset( $groups[ $group ] ) )
        {
            $groups[ $group ] = false;
        }
        
        $this->tag->setTagName( $tag );
        $this->tag->addAttribute( 'class', ( $groups[ $group ] === false ) ? $even : $odd );
        $this->tag->setContent( $this->renderChildren() );
        
        $groups[ $group ] = ( $groups[ $group ] === true ) ? false : true;
        
        return $this->tag->render();
    }
}
