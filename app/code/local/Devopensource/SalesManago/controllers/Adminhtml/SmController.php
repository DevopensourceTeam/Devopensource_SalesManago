<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
ini_set('display_errors', 1);
ini_set('max_execution_time', 3600);
ini_set('max_input_time', 3600);
ini_set("memory_limit",'4048M');

class Devopensource_SalesManago_Adminhtml_SmController extends Mage_Adminhtml_Controller_Action {

    public function exportproductsmAction()
    {
        $productsId         = $this->getRequest()->getPost('product');
        $file               = Mage::helper('devopensalesmanago/products')->exportProducts(true, $productsId);

        $this->getResponse ()
            ->setHttpResponseCode ( 200 )
            ->setHeader ( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true )
            ->setHeader ( 'Pragma', 'public', true )
            ->setHeader ( 'Content-type', 'application/force-download' )
            ->setHeader ( 'Content-Length', filesize($file) )
            ->setHeader ('Content-Disposition', 'attachment' . '; filename=' . basename($file) );
        $this->getResponse ()->clearBody ();
        $this->getResponse ()->sendHeaders ();
        readfile ( $file );

    }

    public function exportorderAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        Mage::helper('devopensalesmanago')->genXlsOrders($orderIds);
    }


    public function exportorderyearAction(){
        Mage::helper('devopensalesmanago')->genXlsOrders(null, '2016');
    }

    protected function _isAllowed()
    {
        // return Mage::getSingleton('admin/session')->isAllowed('catalog/devopensource_import/devopensource_import');
        return true;
    }


}