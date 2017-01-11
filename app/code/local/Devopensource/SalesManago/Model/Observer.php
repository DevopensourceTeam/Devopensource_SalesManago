<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Devopensource_SalesManago_Model_Observer {

    protected function _getHelper()
    {
        return Mage::helper('devopensalesmanago');
    }

    public function customerEvent(Varien_Event_Observer $observer){
        $helper = $this->_getHelper();
        if(!$helper->isEnabled() || !$helper->isEnabledRegister()){
            return false;
        }


        /**
         * @var $customer Mage_Customer_Model_Customer
         */
        $customer = $observer->getEvent()->getDataObject();

        $checkFields = array('email','firstname','lastname','middlename','dob','country_id','region');
        if(!$this->_getHelper()->checkData($customer,$checkFields)){
            return false;
        };

        $data = $this->_getHelper()->setCustomerData($customer);

        if($customer->isObjectNew()){
            $data['tags'] = $helper->addTags($helper->getTagRegistration());
            $data['tags'] = $helper->addTags($helper->getTagOptout(),$data['tags']);
            $data = $helper->addOpt($data,Devopensource_SalesManago_Helper_Data::OPT_OUT);
            $result = $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);
        }else{
            $result = $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);
        }

        if (isset($result['contactId']) && !empty($result['contactId'])) {
            try {
                $this->_getHelper()->setCookie($result['contactId']);
                $smContact = $customer->getData('salesmanago_contact_id');
                if(!isset($smContact) || $smContact==""){
                    $customer->setData('salesmanago_contact_id', $result['contactId']);
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }
        }
    }

    public function newletterEvent(Varien_Event_Observer $observer)
    {
        $helper = $this->_getHelper();
        if (!$helper->isEnabled() || !$helper->isEnabledNewsletter()) {
            return false;
        }
        /**
         * @var $subcriber Mage_Newsletter_Model_Subscriber
         */
        $subcriber = $observer->getEvent()->getDataObject();

        if($this->_getHelper()->checkEqualData($subcriber,'newsletter/subscriber')){
            return false;
        }

        if($subcriber->getCustomerId()){

            $customer = Mage::getModel('customer/customer')->load($subcriber->getCustomerId());
            $data = $this->_getHelper()->setCustomerData($customer);

            if($subcriber->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
                $data['tags'] = $helper->addTags($helper->getTagSubscribe());
                $data['removeTags'] = $helper->addTags($helper->getTagOptout());
                $data['removeTags'] = $helper->addTags($helper->getTagSubscribeGuest(),$data['removeTags']);
                $data = $helper->addOpt($data,Devopensource_SalesManago_Helper_Data::OPT_IN);
                $result = $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);
            }else{
                $data['removeTags'] = $helper->addTags($helper->getTagSubscribe());
                $data['tags'] = $helper->addTags($helper->getTagOptout());
                $data = $helper->addOpt($data,Devopensource_SalesManago_Helper_Data::OPT_OUT);
                $result = $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);
            }
        }else{
            $data['contact']['email'] = $subcriber->getSubscriberEmail();

            if($subcriber->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
                $data['tags'] = $helper->addTags($helper->getTagSubscribeGuest());
                $data['removeTags'] = $helper->addTags($helper->getTagOptout());
                $data = $helper->addOpt($data,Devopensource_SalesManago_Helper_Data::OPT_IN);
                $result = $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);
            }else{
                $data['removeTags'] = $helper->addTags($helper->getTagSubscribeGuest());
                $data['tags'] = $helper->addTags($helper->getTagOptout());
                $data = $helper->addOpt($data,Devopensource_SalesManago_Helper_Data::OPT_OUT);
                $result = $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);
            }
        }

        if (isset($result['contactId']) && !empty($result['contactId'])) {
                $this->_getHelper()->setCookie($result['contactId']);
        }
    }

    public function purchaseEvent(Varien_Event_Observer $observer)
    {
        $helper = $this->_getHelper();
        if (!$helper->isEnabled() || !$helper->isEnabledPurchase()) {
            return false;
        }

        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        if($order->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $data = $this->_getHelper()->setCustomerData($customer);
        }else{
            $data['contact']['email'] = $customerEmail = $order->getCustomerEmail();
        }

        $data['tags'] = $helper->addTags($helper->getTagPurchase());
        $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);

        $dataEvent = array();
        $dataEvent['email'] = $data['contact']['email'];
        $dataEvent['contactEvent'] = $this->_getHelper()->setPurchaseData($order);

        $this->_getHelper()->salesmanagoSync($dataEvent,Devopensource_SalesManago_Helper_Data::PATH_CONTACT_EVENT);

    }

    public function cartEvent(Varien_Event_Observer $observer)
    {
        $helper = $this->_getHelper();
        if (!$helper->isEnabled() || !$helper->isEnabledCard()) {
            return false;
        }

        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if(!$isLoggedIn){
            return false;
        }

        $cart= Mage::getModel('checkout/cart')->getQuote();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $data = $this->_getHelper()->setCustomerData($customer);

        $data['tags'] = $helper->addTags($helper->getTagCart());
        $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);

        $dataEvent = array();
        $dataEvent['contactEvent'] = $this->_getHelper()->setCartData($cart);

        $eventId = Mage::getSingleton('core/session')->getEventCartId();
        if (isset($eventId) && !empty($eventId)) {
            $dataEvent['contactEvent']['eventId'] = $eventId;
            $result = $this->_getHelper()->salesmanagoSync($dataEvent,Devopensource_SalesManago_Helper_Data::PATH_EDIT_EVENT);
        }else{
            $dataEvent['email'] = $customer->getEmail();
            $result = $this->_getHelper()->salesmanagoSync($dataEvent,Devopensource_SalesManago_Helper_Data::PATH_CONTACT_EVENT);
        }

        if (!isset($eventId) && isset($result['eventId'])) {
            Mage::getSingleton('core/session')->setEventCartId($result['eventId']);
        }
    }

    public function whislistEvent(Varien_Event_Observer $observer)
    {
        $helper = $this->_getHelper();
        if (!$helper->isEnabled() || !$helper->isEnabledCard()) {
            return false;
        }

        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if(!$isLoggedIn){
            return false;
        }

        $product = $observer->getEvent()->getItems()[0]->getProduct();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $data = $this->_getHelper()->setCustomerData($customer);

        $data['tags'] = $helper->addTags($helper->getTagWhislist());
        $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);

        $dataEvent = array();
        $dataEvent['email'] = $customer->getEmail();
        $dataEvent['contactEvent'] = $this->_getHelper()->setWishlistData($product);

        $this->_getHelper()->salesmanagoSync($dataEvent,Devopensource_SalesManago_Helper_Data::PATH_CONTACT_EVENT);
    }

    public function customerLoginEvent(Varien_Event_Observer $observer){
        $helper = $this->_getHelper();
        if(!$helper->isEnabled() || !$helper->isEnabledRegister()){
            return false;
        }

        $customer = $observer->getCustomer();
        $smContact = $customer->getData('salesmanago_contact_id');
        if(!isset($smContact) || $smContact==""){
            $data = $this->_getHelper()->setCustomerData($customer);
            $result = $this->_getHelper()->salesmanagoSync($data,Devopensource_SalesManago_Helper_Data::PATH_NEW_OR_UPDATE);

            if (isset($result['contactId']) && !empty($result['contactId'])) {
                try {
                    $this->_getHelper()->setCookie($result['contactId']);
                    $smContact = $customer->getData('salesmanago_contact_id');
                    if(!isset($smContact) || $smContact==""){
                        $customer->setData('salesmanago_contact_id', $result['contactId']);
                    }
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }
            }

        }else{
            $this->_getHelper()->setCookie();
        }
    }

    public function actionExportSm(Varien_Event_Observer $observer)
    {
        $helper = $this->_getHelper();

        if (!$helper->isEnabled()) {
            return false;
        }

        $block = $observer->getEvent()->getBlock();

        if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction
            && $block->getRequest()->getControllerName() == 'catalog_product')
        {
            $block->addItem('exportprodsm', array(
                'label' => 'Export XML SM',
                'url' => $block->getUrl('adminhtml/sm/exportproductsm', array('_current'=>true))
            ));
        }

    }
}