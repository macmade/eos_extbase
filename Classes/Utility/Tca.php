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
 * TCA utility
 *
 * @author      Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
class Tx_EosExtbase_Utility_Tca
{
    protected static function _tcaCheck( $extKey, $table )
    {
        if( !isset( $GLOBALS[ 'TCA' ][ $table ] ) )
        {
            $GLOBALS[ 'TCA' ][ $table ] = array();
        }
        
        if( !isset( $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ] ) || !is_array( $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ] ) )
        {
            $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ] = array();
        }
        
        if( !isset( $GLOBALS[ 'TCA' ][ $table ][ 'columns' ] ) || !is_array( $GLOBALS[ 'TCA' ][ $table ][ 'columns' ] ) )
        {
            $GLOBALS[ 'TCA' ][ $table ][ 'columns' ] = array();
        }
    }
    
    protected static function _getFullTableName( $extKey, $table )
    {
        return 'tx_' . str_replace( '_', '', strtolower( $extKey ) ) . '_domain_model_' . $table;
    }
    
    public static function setTypeField( $extKey, $table, $field )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $GLOBALS[ 'TCA' ][ $table ][ 'ctrl' ][ 'type' ] = ( string )$field;
    }
    
    public static function enableLocalisation( $extKey, $table )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ 'sys_language_uid' ] = array
        (
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.php:LGL.language',
            'config'  => array
            (
                'type'                  => 'select',
                'foreign_table'         => 'sys_language',
                'foreign_table_where'   => 'ORDER BY sys_language.title',
                'items' => array
                (
                    array( 'LLL:EXT:lang/locallang_general.php:LGL.allLanguages', -1 ),
                    array( 'LLL:EXT:lang/locallang_general.php:LGL.default_value', 0 )
                )
            )
        );
        
        $columns[ 'l18n_parent' ] = array
        (
            'displayCond'   => 'FIELD:sys_language_uid:>:0',
            'exclude'       => 1,
            'label'         => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
            'config'        => array
            (
                'type'                  => 'select',
                'items'                 => array( array( '', 0 ) ),
                'foreign_table'         => $table,
                'foreign_table_where'   => 'AND ' . $table . '.uid=###REC_FIELD_l18n_parent### AND ' . $table . '.sys_language_uid IN (-1,0)',
            )
        );
        
        $columns[ 'l18n_diffsource' ] = array
        (
            'config'    => array
            (
                'type'  => 'passthrough'
            )
        );
    }
    
    public static function enableVersioning( $extKey, $table )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ 't3ver_label' ] = array
        (
            'displayCond'   => 'FIELD:t3ver_label:REQ:true',
            'label'         => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
            'config'        => array
            (
                'type'  =>'none',
                'cols'  => 27
            )
        );
    }
    
    public static function addHiddenField( $extKey, $table )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ 'hidden' ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'    => array
            (
                'type'  => 'check'
            )
        );
    }
    
    public static function addTextInput( $extKey, $table, $name, $eval = '' )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'  => 'input',
                'size'  => 30,
                'eval'  => $eval
            )
        );
    }
    
    public static function addText( $extKey, $table, $name )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'  => 'text'
            )
        );
    }
    
    public static function addInlineRelation( $extKey, $table, $name, $foreignTable, $foreignField )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'          => 'inline',
                'foreign_table' => $foreignTable,
                'foreign_field' => $foreignField,
                'maxitems'      => 9999,
                'appearance'    => array
                (
                    'collapse'              => 0,
                    'newRecordLinkPosition' => 'bottom',
                    'useSortable'           => 1
                )
            )
        );
    }
    
    public static function addInlineMMRelation( $extKey, $table, $name, $foreignTable, $mmTable )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'          => 'inline',
                'foreign_table' => $foreignTable,
                'MM'            => $mmTable,
                'maxitems'      => 9999,
                'appearance'    => array
                (
                    'collapse'              => 0,
                    'newRecordLinkPosition' => 'bottom',
                    'useSortable'           => 1
                )
            )
        );
    }
    
    public static function addRelation( $extKey, $table, $name, $foreignTable, $maxItems = 1 )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'          => 'select',
                'foreign_table' => $foreignTable,
                'maxitems'      => $maxItems
            )
        );
    }
    
    public static function addSingleSelect( $extKey, $table, $name, $numValues, $asCheckbox = false )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        $items = array();
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        for( $i = 0; $i < ( int )$numValues; $i++ )
        {
            $items[ $i ] = array
            (
                'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name . '.I.' . $i,
                $i
            );
        }
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'     => 'select',
                'size'     => 1,
                'maxitems' => 1,
                'items'    => $items
            )
        );
    }
    
    public static function addNone( $extKey, $table, $name )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type' => 'none'
            )
        );
    }
    
    public static function addImage( $extKey, $table, $name, $maxSize = 1000 )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'          => 'group',
                'internal_type' => 'file',
                'allowed'       => 'jpg,jpeg,gif,png',
                'max_size'      => $maxSize,
                'uploadfolder'  => 'uploads/tx_' . str_replace( '_', '', $extKey ),
                'show_thumbs'   => 1,
                'size'          => 1,
                'maxitems'      => 1
            )
        );
    }
    
    public static function addImages( $extKey, $table, $name, $maxSize = 1000, $maxItems = 100 )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'          => 'group',
                'internal_type' => 'file',
                'allowed'       => 'jpg,jpeg,gif,png',
                'max_size'      => $maxSize,
                'uploadfolder'  => 'uploads/tx_' . str_replace( '_', '', $extKey ),
                'show_thumbs'   => 1,
                'size'          => 10,
                'maxitems'      => $maxItems
            )
        );
    }
    
    public static function addCheckBox( $extKey, $table, $name, $checked = false )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'      => 'check',
                'default'   => ( bool )$checked
            )
        );
    }
    
    public static function addDate( $extKey, $table, $name )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'      => 'input',
                'size'      => 8,
                'max'       => 20,
                'eval'      => 'date',
                'checkbox'  => '0',
                'default'   => '0'
            )
        );
    }
    
    public static function addLink( $extKey, $table, $name )
    {
        $table =  self::_getFullTableName( $extKey, $table );
        
        self::_tcaCheck( $extKey, $table );
        
        $columns =& $GLOBALS[ 'TCA' ][ $table ][ 'columns' ];
        
        $columns[ $name ] = array
        (
            'exclude'   => 1,
            'label'     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $table . '.xml:' . $table . '.' . $name,
            'config'    => array
            (
                'type'      => 'input',
                'size'      => 30,
                'eval'      => 'trim',
                'checkbox'  => '',
                'max'       => '256',
                'wizards'   => array
                (
                    '_PADDING'  => 2,
                    'link'      => array
                    (
                        'type'          => 'popup',
                        'title'         => 'Link',
                        'icon'          => 'link_popup.gif',
                        'script'        => 'browse_links.php?mode=wizard',
                        'JSopenParams'  => 'height=500,width=500,status=0,menubar=0,scrollbars=1'
                    )
                )
            )
        );
    }
}
