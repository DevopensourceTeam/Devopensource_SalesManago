<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Devopensource_SalesManago_ExportController  extends Mage_Core_Controller_Front_Action {

    public function productsAction(){
        $token              = $this->getRequest()->getParam('token');
        $active             = Mage::getStoreConfig('devopensalesmanago/sync_products/active_sync');
        $tokenMgt           = Mage::getStoreConfig('devopensalesmanago/sync_products/token');
        $activeModule       = Mage::getStoreConfig('devopensalesmanago/general/active');

        /*
         * Punto de entrada
         * */
        if($activeModule && $active && $tokenMgt == $token){

            $filePath   = Mage::getBaseDir('var') . DS . 'export' . DS . 'products_salesManago.xml';
            $io         = $this->_getIo();

            if($io->fileExists($filePath)){
                $filePath = file_get_contents($filePath);
                $this->getResponse()->clearHeaders()->setHeader('Content-type','application/xml',true);
                $this->getResponse()->setBody($filePath);
            }
        }else{

            $this->norouteAction();
            return;
        }
    }

    protected function _getIo(){
        return new Varien_Io_File();
    }
}