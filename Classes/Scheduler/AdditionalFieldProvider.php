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
 * Scheduler additional fields provider
 *
 * @author      Jean-David Gadina <macmade@eosgarden.com>
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
abstract class Tx_EosExtbase_Scheduler_AdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider
{
    protected $_fields = array();
    
    /**
     * 
     */
    public function getAdditionalFields( array &$taskInfo, $task, tx_scheduler_Module $parentObject )
    {
        $fields   = array();
        $class    = get_class( $this );
        $extName  = substr( $class, 3, strpos( $class, '_', 3 ) - 3 );
        $extKey   = t3lib_div::camelCaseToLowerCaseUnderscored( $extName );
        $taskName = substr( $class, strrpos( $class, '_', -8 ) + 1, -7 );
        
        foreach( $this->_fields as $field => $defaultValue )
        {
            if( empty( $taskInfo[ $field ] ) )
            {
                if( $parentObject->CMD === 'add' )
                {
                    $taskInfo[ $field ] = $defaultValue;
                }
                elseif( $parentObject->CMD === 'edit' )
                {
                    $taskInfo[ $field ] = $task->$field;
                }
                else
                {
                    $taskInfo[ $field ] = '';
                }
            }
            
            $fields[ 'task_' . $field ] = array
            (
                'code'     => '<input name="tx_scheduler[' . $field . ']" id="task_' . $field . '" type="text" size="20" value="' . $taskInfo[ $field ] . '" />',
                'label'    => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/scheduler.xml:' . $taskName . '.fields.' . $field,
                'cshKey'   => '',
                'cshLabel' => '',
            );
        }
        
        return $fields;
    }
    
    /**
     * 
     */
    public function validateAdditionalFields( array &$submittedData, tx_scheduler_Module $parentObject )
    {
        return true;
    }
    
    /**
     * 
     */
    public function saveAdditionalFields( array $submittedData, tx_scheduler_Task $task )
    {
        foreach( $this->_fields as $field => $void )
        {
            $task->$field = $submittedData[ $field ];
        }
    }
}
