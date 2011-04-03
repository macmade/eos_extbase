<?php

require_once( PATH_tslib . 'class.tslib_eidtools.php' ); 

$EID = t3lib_div::_GP( 'eID' );

if( defined( 'TYPO3_MODE' ) && $EID && isset( $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'FE' ][ 'eID_include' ][ $EID ] ) )
{
    if( !class_exists( $EID ) )
    {
        throw new Tx_EosExtbase_Eid_Exception
        (
            'EID class ' . $EID . ' does not exist',
            Tx_EosExtbase_Eid_Exception::EXCEPTION_NO_CLASS
        );
    }
    
    if( !is_subclass_of( $EID, 'Tx_EosExtbase_Eid_Base' ) )
    {
        throw new Tx_EosExtbase_Eid_Exception
        (
            'EID class ' . $EID . ' must extend Tx_EosExtbase_Eid_Base',
            Tx_EosExtbase_Eid_Exception::EXCEPTION_INVALID_CLASS
        );
    }
    
    $OBJ = new $EID();
    
    $OBJ->execute();
    
    print $OBJ;
}
