<?php

# $Id$

// DEBUG ONLY - Sets the error reporting level to the highest possible value
#error_reporting( E_ALL | E_STRICT );

// Checks if TYPO3 is running
if( !defined( 'TYPO3_MODE' ) )
{
    // TYPO3 is not running
    trigger_error(
        'TYPO3 does not seem to be running. This script can only be used with TYPO3.',
        E_USER_ERROR
    );
}

// Checks the PHP version
if( ( double )PHP_VERSION < 5.2 )
{
    // PHP version too low
    trigger_error(
        'PHP version 5.2 is required to use this script (actual version is ' . PHP_VERSION . ')',
        E_USER_ERROR
    );
}

// Checks for the SPL
if( !function_exists( 'spl_autoload_register' ) )
{
    // The SPL is unavailable
    throw new Exception( 'The SPL (Standard PHP Library) is required to use this script' );
}

// Includes the class manager
require_once(
    t3lib_extMgm::extPath( $_EXTKEY )
  . DIRECTORY_SEPARATOR
  . 'Classes'
  . DIRECTORY_SEPARATOR
  . 'Core'
  . DIRECTORY_SEPARATOR
  . 'ClassManager.php'
);

// Registers an SPL autoload method to use to load the classes form this package
spl_autoload_register( array( 'Tx_EosExtbase_Core_ClassManager', 'autoLoad' ) );

// Registers an SPL autoload method to use to load the classes form TYPO3 (t3lib or tslib)
spl_autoload_register( array( 'Tx_EosExtbase_Typo3_ClassManager', 'autoLoad' ) );

?>
