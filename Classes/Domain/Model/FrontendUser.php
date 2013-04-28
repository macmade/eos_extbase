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
 * FE user model
 *
 * @author      (c) 2009 - Jean-David Gadina - www.xs-labs.com
 * @version     1.0
 * @package     TYPO3
 * @subpackage  eos_extbase
 */
class Tx_EosExtbase_Domain_Model_FrontendUser extends Tx_EosExtbase_DomainObject_AbstractEntity
{
    /**
     * @var string
     */
    protected $username             = '';
    
    /**
     * @var string
     */
    protected $password             = '';
    
    /**
     * @var integer
     */
    protected $usergroup            = 0;
    
    /**
     * @var string
     */
    protected $name                 = '';
    
    /**
     * @var string
     */
    protected $address              = '';
    
    /**
     * @var string
     */
    protected $telephone            = '';
    
    /**
     * @var string
     */
    protected $fax                  = '';
    
    /**
     * @var string
     */
    protected $email                = '';
    
    /**
     * @var string
     */
    protected $title                = '';
    
    /**
     * @var string
     */
    protected $zip                  = '';
    
    /**
     * @var string
     */
    protected $city                 = '';
    
    /**
     * @var string
     */
    protected $country              = '';
    
    /**
     * @var string
     */
    protected $www                  = '';
    
    /**
     * @var string
     */
    protected $company              = '';
    
    /**
     * @var string
     */
    protected $image                = '';
    
    /**
     * 
     */
    protected $_userGroupRepository = NULL;
    
    public function __construct( $username = '', $password = '' )
    {
        $this->username             = ( string )$username;
        $this->password             = ( string )$password;
        $this->_userGroupRepository = t3lib_div::makeInstance( 'Tx_EosExtbase_Domain_Repository_FrontendUserGroupRepository' );
    }
    
    /**
     * @return  Tx_EosExtbase_Domain_Model_FrontendUserGroup
     */
    public function getUsergroup()
    {
        return $this->_userGroupRepository->findByUid( $this->usergroup, false );
    }
    
    /**
     * @param   Tx_EosExtbase_Domain_Model_FrontendUserGroup    $usergroup
     * @return  void
     */
    public function setUsergroup( Tx_EosExtbase_Domain_Model_FrontendUserGroup $usergroup )
    {
        $this->usergroup = $usergroup->getUid();
    }
}
