<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Devopensource_SalesManago_Model_Cron {

    public function exportProducts(){

        $active             = Mage::getStoreConfig('devopensalesmanago/sync_products/active_sync');
        $activeModule       = Mage::getStoreConfig('devopensalesmanago/general/active');

        if( $active && $activeModule ){
            Mage::helper('devopensalesmanago/products')->exportProducts();
        }

    }
}