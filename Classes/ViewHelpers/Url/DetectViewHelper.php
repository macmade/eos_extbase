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
 * View helper for the URL detection
 * 
 * @author      Jean-David Gadina <macmade@eosgarden.com>
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
class Tx_EosExtbase_ViewHelpers_Url_DetectViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
{
    protected $cObj = NULL;
    
    public function __construct()
    {
        $this->_cObj = t3lib_div::makeInstance( 'tslib_cObj' );
    }
    
    protected function _replaceLinks( $matches )
    {
        if( isset( $matches[ 1 ] ) )
        {
            $url = $matches[ 0 ];
        }
        else
        {
            $url = 'http://' . $matches[ 0 ];
        }
        
        return '<a href="' . $url . '">' . $matches[ 0 ] . '</a>';
    }
    
    protected function _replaceEmails( $matches )
    {
        $parts = $this->_cObj->getMailTo( $matches[ 0 ] );
        
        return '<a href="' . $parts[ 0 ] . '">' . $parts[ 1 ] . '</a>';
    }
    
    public function render()
    {
        $content = $this->renderChildren();
        $content = preg_replace_callback(
            ';(?<![">])\b(?:(?:(https?|ftp|file))://|www\.|ftp\.)[-A-Z0-9+&@#/%=~_|$?!:,.]*[A-Z0-9+&@#/%=~_|$];i',
            array(
                $this,
                '_replaceLinks'
            ),
            $content
        );
        $content = preg_replace_callback(
            ';[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?;i',
            array(
                $this,
                '_replaceEmails'
            ),
            $content
        );
        
        return $content;
    }
}
