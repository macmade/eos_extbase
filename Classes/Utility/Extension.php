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
 * Extension utility
 *
 * @author      (c) 2009 - Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
class Tx_EosExtbase_Utility_Extension
{
    /**
     * 
     */
    public static function getControllersAndActions( $extKey )
    {
        $controllerActions    = array( array(), array() );
        
        $path              = t3lib_extMgm::extPath( $extKey ) . 'Classes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR;
        $controllers       = glob( $path . '*Controller.php' );
        $defaultController = '';
        
        foreach( $controllers as $controllerFile )
        {
            $controller = substr( basename( $controllerFile ), 0, -14 );
            $className = 'Tx_' . t3lib_div::underscoredToUpperCamelCase( $extKey ) . '_Controller_' . $controller . 'Controller';
            
            try
            {
                $ref = new Tx_Extbase_Reflection_ClassReflection( $className );
            }
            catch( ReflectionException $e )
            {
                continue;
            }
            
            $methods    = $ref->getMethods( ReflectionMethod::IS_PUBLIC );
            $actions    = array();
            $actionsInt = array();
            
            if( $ref->isTaggedWith( 'default' ) )
            {
                $defaultController = $controller;
            }
            
            $defaultAction = '';
            
            foreach( $methods as $method )
            {
                if( $method->getName() === 'initializeAction' )
                {
                    continue;
                }
                
                if( substr( $method->getName(), -6 ) !== 'Action' )
                {
                    continue;
                }
                
                if( $method->isTaggedWith( 'default' ) )
                {
                    $defaultAction = $method->getName();
                }
                
                $actions[ $method->getName() ] = substr( $method->getName(), 0, -6 );
                
                if( $method->isTaggedWith( 'nocache' ) )
                {
                    $actionsInt[ $method->getName() ] = substr( $method->getName(), 0, -6 );
                }
            }
            
            if( $defaultAction )
            {
                unset( $actions[ $defaultAction ] );
                
                $actions = array_merge( array( $defaultAction => substr( $defaultAction, 0, -6 ) ), $actions );
            }
            
            $controllerActions[ 0 ][ $controller ] = $actions;
            
            if( count( $actionsInt ) )
            {
                $controllerActions[ 1 ][ $controller ] = $actionsInt;
            }
        }
        
        if( $defaultController )
        {
            $default = array( $defaultController => $controllerActions[ 0 ][ $defaultController ] );
            
            unset( $controllerActions[ 0 ][ $defaultController ] );
            
            $controllerActions[ 0 ] = array_merge( $default, $controllerActions[ 0 ] );
        }
        
        return $controllerActions;
    }
    
    /**
     * 
     */
    public static function configurePlugin( $extKey, $pluginName )
    {
        $extensionName                = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $extKey ) ) );
        $pluginSignature              = strtolower( $extensionName ) . '_' . strtolower( $pluginName );
        $controllerCounter            = 1;
        $hasMultipleActionsCounter    = 0;
        $controllers                  = '';
        $controllersAndActions        = self::getControllersAndActions( $extKey );
        $controllerActions            = array();
        $nonCachableControllerActions = array();
        
        foreach( $controllersAndActions[ 0 ] as $controller => $actions )
        {
            $controllerActions[ $controller ] = implode( ',', $actions );
        }
        
        foreach( $controllersAndActions[ 1 ] as $controller => $actions )
        {
            $nonCachableControllerActions[ $controller ] = implode( ',', $actions );
        }
        
        foreach( $controllerActions as $controller => $actionsList )
        {
            $controllers .= chr( 10 )
                         .  '        '
                         .  $controllerCounter
                         .  '.controller = '
                         .  $controller
                         .  chr( 10 )
                         .  '        '
                         .  $controllerCounter
                         .  '.actions    = '
                         .  $actionsList;
            
            $controllerCounter++;
            
            if( strpos( $actionsList, ',' ) !== false )
            {
                $hasMultipleActionsCounter++;
            }
        }
        
        $switchableControllerActions = '';
        
        if( $controllerCounter > 1 || $hasMultipleActionsCounter > 0 )
        {
            $switchableControllerActions = chr( 10 ) . '    switchableControllerActions {' . $controllers . chr( 10 ) . '    }';
        }
        
        reset( $controllerActions );
        
        $defaultController = key( $controllerActions );
        
        $controller         = chr( 10 ) . '    controller    = ' . $defaultController;
        $defaultAction      = array_shift( t3lib_div::trimExplode( ',', current( $controllerActions ) ) );
        $action             = chr( 10 ) . '    action        = ' . $defaultAction;
        $nonCachableActions = array();
        
        if( !empty( $nonCachableControllerActions[ $defaultController ] ) )
        {
            $nonCachableActions = t3lib_div::trimExplode( ',', $nonCachableControllerActions[ $defaultController ] );
        }
        
        $cachableActions   = array_diff( t3lib_div::trimExplode( ',', $controllerActions[ $defaultController ] ), $nonCachableActions );
        $contentObjectType = in_array( $defaultAction, $nonCachableActions ) ? 'USER_INT' : 'USER';
        $conditions        = '';
        
        foreach( $controllerActions as $controllerName => $actionsList )
        {
            if( !empty( $nonCachableControllerActions[ $controllerName ] ) )
            {
                $nonCachableActions = t3lib_div::trimExplode( ',', $nonCachableControllerActions[ $controllerName ] );
                $cachableActions    = array_diff( t3lib_div::trimExplode( ',', $controllerActions[ $controllerName ] ), $nonCachableActions );
                
                if
                (
                       ( $contentObjectType == 'USER'     && count( $nonCachableActions ) > 0 )
                    || ( $contentObjectType == 'USER_INT' && count( $cachableActions )    > 0 )
                )
                {
                    $conditions .= <<<TS

[globalVar = GP:%s|controller = %s] && [globalVar = GP:%s|action = %s]
    tt_content.list.20.%s = %s
[GLOBAL]

TS;
                    
                    $conditions = sprintf
                    (
                        $conditions,
                        'tx_' . $pluginSignature,
                        $controllerName,
                        'tx_' . $pluginSignature,
                        implode( '|', ( $contentObjectType === 'USER' ) ? $nonCachableActions : $cachableActions ),
                        $pluginSignature,
                        ( ( $contentObjectType === 'USER' ) ? 'USER_INT' : 'USER' )
                    );
                }
            }
        }
        
        $pluginTemplate = <<<TS

# TypoScript configuration for extension %s
plugin.%s {
    
    // Plugin specific settings
    settings {
        
    }
    
    // Persistence Framework settings
    persistence {
        
        // Page ID for the records storage
        storagePid =
        
        // Class mapping
        classes {
            
            
        }
    }
    
    // Fluid settings
    view {
        
        // Paths to the templates
        templateRootPath =
        layoutRootPath   =
        partialRootPath  =
    }
    
    // Plugin stylesheet
    _CSS_DEFAULT_STYLE (
        
        @import url( '/%sResources/Public/CSS/styles.css' );
    )
}
TS;
        
        $pluginContent = <<<TS

# TypoScript configuration for extension %s
tt_content.list.20.%s = %s
tt_content.list.20.%s {
    
    userFunc      = tx_EosExtbase_dispatcher->dispatch
    pluginName    = %s
    extensionName = %s
    settings      =< plugin.%s.settings
    persistence   =< plugin.%s.persistence
    view          =< plugin.%s.view
    _LOCAL_LANG   =< plugin.%s._LOCAL_LANG
    %s
}
%s
TS;
        
        t3lib_extMgm::addTypoScript(
            $extensionName,
            'setup',
            sprintf
            (
                $pluginTemplate,
                $extensionName,
                'tx_' . strtolower( $extensionName ),
                t3lib_extMgm::siteRelPath( $extKey )
            )
        );
        
        t3lib_extMgm::addTypoScript(
            $extensionName,
            'setup',
            sprintf
            (
                $pluginContent,
                $extensionName,
                $pluginSignature,
                $contentObjectType,
                $pluginSignature,
                $pluginName,
                $extensionName . $controller . $action,
                'tx_' . strtolower( $extensionName ),
                'tx_' . strtolower( $extensionName ),
                'tx_' . strtolower( $extensionName ),
                'tx_' . strtolower( $extensionName ),
                $switchableControllerActions,
                $conditions
            ),
            43
        );
    }
    
    /**
     * 
     */
    public static function configurePluginForRealUrl( $extKey, $pluginName, array $tableMap = array() )
    {
        if( !t3lib_extMgm::isLoaded( 'realurl' ) )
        {
            return;
        }
        
        if( !isset( $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXTCONF' ][ 'realurl' ][ '_DEFAULT' ] ) )
        {
            return;
        }
        
        if( !isset( $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXTCONF' ][ 'realurl' ][ '_DEFAULT' ][ 'postVarSets' ] ) )
        {
            $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXTCONF' ][ 'realurl' ][ '_DEFAULT' ][ 'postVarSets' ] = array();
        }
        
        if( !isset( $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXTCONF' ][ 'realurl' ][ '_DEFAULT' ][ 'postVarSets' ][ '_DEFAULT' ] ) )
        {
            $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXTCONF' ][ 'realurl' ][ '_DEFAULT' ][ 'postVarSets' ][ '_DEFAULT' ] = array();
        }
        
        $extName     =  t3lib_div::underscoredToUpperCamelCase( $extKey );
        $pluginKey   =  'tx_' . str_replace( '_', '', strtolower( $extKey ) ) . '_' . strtolower( $pluginName );
        $realUrl     =& $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXTCONF' ][ 'realurl' ];
        $postVars    =& $realUrl[ '_DEFAULT' ][ 'postVarSets' ][ '_DEFAULT' ];
        $path        =  t3lib_extMgm::extPath( $extKey ) . 'Classes' . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR;
        $controllers =  glob( $path . '*Controller.php' );
        
        $postVars[ $extName . $pluginName ] = array
        (
            array( 'GETvar' => $pluginKey . '[controller]' ),
            array( 'GETvar' => $pluginKey . '[action]' )
        );
        
        foreach( $controllers as $controllerFile )
        {
            $controller = substr( basename( $controllerFile ), 0, -14 );
            $className = 'Tx_' . $extName . '_Controller_' . $controller . 'Controller';
            
            try
            {
                $ref = new Tx_Extbase_Reflection_ClassReflection( $className );
            }
            catch( ReflectionException $e )
            {
                continue;
            }
            
            $methods    = $ref->getMethods( ReflectionMethod::IS_PUBLIC );
            
            foreach( $methods as $method )
            {
                if( $method->getName() === 'initializeAction' )
                {
                    continue;
                }
                
                if( substr( $method->getName(), -6 ) !== 'Action' )
                {
                    continue;
                }
                
                $params = $method->getParameters();
                
                foreach( $params as $param )
                {
                    $conf = array( 'GETvar' => $pluginKey . '[' . $param->getName() . ']' );
                    
                    if( isset( $tableMap[ $param->getName() ] ) )
                    {
                        $tableInfos = explode( '.', $tableMap[ $param->getName() ] );
                        
                        if( count( $tableInfos === 2 ) )
                        {
                            $table = $tableInfos[ 0 ];
                            $field = $tableInfos[ 1 ];
                            
                            $conf[ 'lookUpTable' ] = array
                            (
                                'table'               => $table,
                                'id_field'            => 'uid',
                                'alias_field'         => $field,
                                'addWhereClause'      => ' AND NOT deleted',
                                'useUniqueCache'      => true,
                                'useUniqueCache_conf' => array(
                                    'spaceCharacter'  => '-'
                                )
                            );
                        }
                    }
                    
                    $postVars[ $extName . $pluginName ][] = $conf;
                }
            }
        }
    }
    
    /**
     * Checks if TYPO3 is running in a specific mode
     * 
     * @param   string  Optional - the TYPO3 run mode (BE, FE)
     */
    public static function typo3ModeCheck( $mode = '' )
    {
        // Security check
        if( !defined( 'TYPO3_MODE' ) )
        {
            // TYPO3 is not running
            trigger_error
            (
                'TYPO3 does not seem to be running. This script can only be used with TYPO3.',
                E_USER_ERROR
            );
        }
        
        // Backend mode check
        if( $mode === 'BE' && TYPO3_MODE !== 'BE' )
        {
            // TYPO3 is not running in a backend context
            trigger_error
            (
                'TYPO3 does not seem to be running in a backend context. This script can only be used with the TYPO3 backend.',
                E_USER_ERROR
            );
        }
        
        // Frontend mode check
        if( $mode === 'FE' && TYPO3_MODE !== 'FE' )
        {
            // TYPO3 is not running in a frontend context
            trigger_error
            (
                'TYPO3 does not seem to be running in a frontend context. This script can only be used with the TYPO3 frontend.',
                E_USER_ERROR
            );
        }
    }
    
    /**
     * 
     */
    public static function addFlexForm( $extKey, $piNum )
    {
        $extensionName = t3lib_div::underscoredToUpperCamelCase( $extKey );
        $pluginName    = strtolower($extensionName) . '_pi' . $piNum;
        
        $GLOBALS[ 'TCA' ][ 'tt_content' ][ 'types' ][ 'list' ][ 'subtypes_excludelist' ][ $pluginName ] = 'layout,select_key,pages,recursive';
        $GLOBALS[ 'TCA' ][ 'tt_content' ][ 'types' ][ 'list' ][ 'subtypes_addlist' ][ $pluginName ]     = 'pi_flexform';
        
        t3lib_extMgm::addPiFlexFormValue( $pluginName, 'FILE:EXT:' . $extKey . '/Configuration/FlexForms/pi' . $piNum . '.xml');
    }
    
    /**
     * 
     */
    public static function addTable( $extKey, $name, $labelField, $manualSorting = false, $allowedInStandardPages = true, $saveAndNew = true, $csh = true )
    {
        $tableName = 'tx_' . str_replace( '_', '', $extKey ) . '_domain_model_' . $name;
        
        if( is_array( $labelField ) )
        {
            $label         = array_shift( $labelField );
            $labelAlt      = implode( ',', $labelField );
            $labelAltForce = true;
        }
        else
        {
            $label         = $labelField;
            $labelAlt      = '';
            $labelAltForce = false;
        }
        
        if( $allowedInStandardPages )
        {
            t3lib_extMgm::allowTableOnStandardPages( $tableName );
        }
        
        if( $saveAndNew )
        {
            t3lib_extMgm::addUserTSConfig( 'options.saveDocNew.'. $tableName . ' = 1' );
        }
        
        if( $csh )
        {
            t3lib_extMgm::addLLrefForTCAdescr(
                $tableName,
                'EXT:' . $extKey . '/Resources/Private/Language/csh_' . $tableName . '.xml'
            );
        }
        
        $GLOBALS[ 'TCA' ][ $tableName ] = array
        (
            'ctrl' => array
            (
                'title'                     => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/' . $tableName . '.xml:' . $tableName,
                'label'                     => $label,
                'label_alt'                 => $labelAlt,
                'label_alt_force'           => $labelAltForce,
                'tstamp'                    => 'tstamp',
                'crdate'                    => 'crdate',
                'dividers2tabs'             => 1,
                'default_sortby'            => $label,
                'versioningWS'              => 2,
                'versioning_followPages'    => true,
                'origUid'                   => 't3_origuid',
                'languageField'             => 'sys_language_uid',
                'transOrigPointerField'     => 'l18n_parent',
                'transOrigDiffSourceField'  => 'l18n_diffsource',
                'delete'                    => 'deleted',
                'dynamicConfigFile'         => t3lib_extMgm::extPath( $extKey ) . 'Configuration/TCA/' . $tableName . '.php',
                'iconfile'                  => t3lib_extMgm::extRelPath( $extKey ) . 'Resources/Public/Icons/' . $tableName . '.gif',
                'enablecolumns'             => array( 'disabled' => 'hidden' )
            )
        );
        
        if( ( bool )$manualSorting === true )
        {
            $GLOBALS[ 'TCA' ][ $tableName ][ 'ctrl' ][ 'sortby' ] = 'sorting';
            
            unset( $GLOBALS[ 'TCA' ][ $tableName ][ 'ctrl' ][ 'default_sortby' ] );
        }
    }
    
    public static function addSchedulerTask( $extKey, $name, $hasAdditionalFields = false )
    {
        $tasks               =& $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SC_OPTIONS' ][ 'scheduler' ][ 'tasks' ];
        $extensionName       =  t3lib_div::underscoredToUpperCamelCase( $extKey );
        $className           =  'Tx_' . $extensionName . '_Scheduler_' . ucfirst( $name );
        $tasks[ $className ] =  array
        (
                'extension'   => $extKey,
                'title'       => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/scheduler.xml:' . $name . '.title',
                'description' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/scheduler.xml:' . $name . '.description'
        );
        
        if( ( boolean )$hasAdditionalFields === true )
        {
            $tasks[ $className ][ 'additionalFields' ] = $className . '_Fields';
        }
    }
    
    public static function addEidScript( $extKey, $name )
    {
        $extensionName =  t3lib_div::underscoredToUpperCamelCase( $extKey );
        $className     =  'Tx_' . $extensionName . '_Eid_' . ucfirst( $name );
        
        $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'FE' ][ 'eID_include' ][ $className ] = 'EXT:eos_extbase/scripts/eid.inc.php';
    }
}
