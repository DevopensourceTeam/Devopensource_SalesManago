<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Devopensource_SalesManago_Block_Tracking extends Mage_Core_Block_Template {

    public function isEnableTrackingJs(){

        return Mage::getStoreConfig('devopensalesmanago/general/tracking_js');
    }

    public function isEnablePopup(){

        return Mage::getStoreConfig('devopensalesmanago/general/active_popup');
    }

    public function getEndpointPopup(){

        return Mage::getStoreConfig('devopensalesmanago/general/endpoint_popup');
    }

    public function getClientId(){

        return Mage::getStoreConfig('devopensalesmanago/general/client_id');
    }

    public function getClientApiSecret(){
        return Mage::getStoreConfig('devopensalesmanago/general/api_secret');
    }

    public function getClientEmail(){
        return Mage::getStoreConfig('devopensalesmanago/general/email');
    }

    public function getEndPoint(){
        return Mage::getStoreConfig('devopensalesmanago/general/endpoint');
    }

    public function isActive(){
        return Mage::getStoreConfig('devopensalesmanago/general/active');
    }

    public function getTagsRegistration(){
        $tags = Mage::getStoreConfig('devopensalesmanago/registration/tags');
        $tags = explode(',', $tags);
        return $tags;
    }

    public function getTagsNewsletter(){
        $tags = Mage::getStoreConfig('devopensalesmanago/newsletter/tags');
        $tags = explode(',', $tags);
        return $tags;
    }

    public function getTagsPurchase(){
        $tags = Mage::getStoreConfig('devopensalesmanago/purchase/tags');
        $tags = explode(',', $tags);
        return $tags;
    }

    public function getClientSalesManagoId(){
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            return $customerData->getSalesmanagoContactId();
        }

        return false;
    }


}