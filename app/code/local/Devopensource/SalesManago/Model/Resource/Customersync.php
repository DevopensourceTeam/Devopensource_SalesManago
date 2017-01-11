<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Devopensource_SalesManago_Model_Resource_Customersync extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
        $this->_init('devopensalesmanago/customersync', 'customer_sync_id');
    }   
}