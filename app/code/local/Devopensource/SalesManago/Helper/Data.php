<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Devopensource_SalesManago_Helper_Data extends Mage_Core_Helper_Abstract {

    const PATH_NEW_OR_UPDATE = 'api/contact/upsert';
    const PATH_CONTACT_EVENT = 'api/contact/addContactExtEvent';
    const PATH_EDIT_EVENT = 'api/contact/updateContactExtEvent';

    const OPT_IN = "1";
    const OPT_OUT = "0";

    public function isEnabled(){
        return Mage::getStoreConfig('devopensalesmanago/general/active', Mage::app()->getStore());
    }

    public function isEnabledRegister(){
        return Mage::getStoreConfig('devopensalesmanago/registration/active_register', Mage::app()->getStore());
    }

    public function isEnabledNewsletter(){
        return Mage::getStoreConfig('devopensalesmanago/newsletter/active_newsletter', Mage::app()->getStore());
    }

    public function isEnabledPurchase(){
        return Mage::getStoreConfig('devopensalesmanago/purchase/active_purchase', Mage::app()->getStore());
    }

    public function isEnabledCard(){
        return Mage::getStoreConfig('devopensalesmanago/cart/active_cart', Mage::app()->getStore());
    }

    public function isEnabledWhislist(){
        return Mage::getStoreConfig('devopensalesmanago/wishlist/active_wishlist', Mage::app()->getStore());
    }

    public function isEnabledTagNavigation(){
        return Mage::getStoreConfig('devopensalesmanago/navigation/active_navigation', Mage::app()->getStore());
    }

    public function isEnabledContact(){
        return Mage::getStoreConfig('devopensalesmanago/contact/active_contact', Mage::app()->getStore());
    }

    public function getTagRegistration(){
        return Mage::getStoreConfig('devopensalesmanago/registration/tags', Mage::app()->getStore());
    }

    public function getTagOptout(){
        return Mage::getStoreConfig('devopensalesmanago/newsletter/tags_remove', Mage::app()->getStore());
    }

    public function getTagSubscribe(){
        return Mage::getStoreConfig('devopensalesmanago/newsletter/tags', Mage::app()->getStore());
    }

    public function getTagSubscribeGuest(){
        return Mage::getStoreConfig('devopensalesmanago/newsletter/tags_guest', Mage::app()->getStore());
    }

    public function getTagPurchase(){
        return Mage::getStoreConfig('devopensalesmanago/purchase/tags', Mage::app()->getStore());
    }

    public function getTagCart(){
        return Mage::getStoreConfig('devopensalesmanago/cart/tags', Mage::app()->getStore());
    }

    public function getTagWhislist(){
        return Mage::getStoreConfig('devopensalesmanago/wishlist/tags', Mage::app()->getStore());
    }

    public function getClientId(){
        return Mage::getStoreConfig('devopensalesmanago/general/client_id', Mage::app()->getStore());
    }

    public function getApiSecret(){
        return Mage::getStoreConfig('devopensalesmanago/general/api_secret', Mage::app()->getStore());
    }

    public function getOwnerEmail(){
        return Mage::getStoreConfig('devopensalesmanago/general/email', Mage::app()->getStore());
    }

    public function getEndPoint(){
        return Mage::getStoreConfig('devopensalesmanago/general/endpoint', Mage::app()->getStore());
    }

    public function setCustomerData($customer){
        $data = array();
        $contact = array();

        $contact['email'] = $customer['email'];

        if(isset($customer['firstname']) && isset($customer['lastname']) && isset($customer['middlename'])){
            $contact['name'] = $customer['firstname'].' '.$customer['middlename'].' '.$customer['lastname'];
        } else {
            $contact['name'] = $customer['firstname'].' '.$customer['lastname'];
        }

        foreach ($customer->getAddresses() as $address) {
            $addressData = $address->toArray();
        }

        if (isset($addressData['telephone'])) {
            $contact['phone'] = $addressData['telephone'];
        }

        if (isset($addressData['fax'])) {
            $contact['fax'] = $addressData['fax'];
        }

        if (isset($addressData['company'])) {
            $contact['company'] = $addressData['company'];
        }

        if(isset($addressData['street']) || isset($addressData['postcode']) || isset($addressData['city']) || isset($addressData['country'])){
            if (isset($addressData['street'])) {
                $contact['address']['streetAddress'] = $addressData['street'];
            }

            if (isset($addressData['postcode'])) {
                $contact['address']['zipCode'] = $addressData['postcode'];
            }

            if (isset($addressData['city'])) {
                $contact['address']['city'] = $addressData['city'];
            }

            if (isset($addressData['country_id'])) {
                $contact['address']['country'] = $addressData['country_id'];
            }
        }


        if(isset($customer['dob'])){
            $dataArray = date_parse($customer['dob']);
            $month  = ($dataArray['month'] < 10) ? "0".$dataArray['month'] : $dataArray['month'];
            $day  = ($dataArray['day'] < 10) ? "0".$dataArray['day'] : $dataArray['day'];
            $year = $dataArray['year'];
            $data['birthday'] = $year . $month .  $day;
        }

        if (isset($addressData['region'])) {
            $data['province'] = $addressData['region'];
        }

        $data['contact'] = $contact;

        return $data;
    }

    public function setPurchaseData($order){
        $dateTime = new DateTime('NOW');
        $productsIdsList = array();
        $productsNamesList = array();
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            array_push($productsIdsList, $item->getProduct()->getSku());
            array_push($productsNamesList, $item->getProduct()->getName());
        }

        $grandTotal = $order->getBaseGrandTotal();
        $incrementOrderId = $order->getIncrementId();


        $data = array(
            'date' => $dateTime->format('c'),
            'products' => implode(',', $productsIdsList),
            'contactExtEventType' => 'PURCHASE',
            'value' => $grandTotal,
            'detail1' => implode(",", $productsNamesList),
            'externalId' => $incrementOrderId
        );

        return $data;

    }

    public function setCartData($cart){
        $dateTime = new DateTime('NOW');
        $productsIdsList = array();
        $productsNamesList = array();
        $items = $cart->getAllVisibleItems();

        foreach ($items as $item) {
            array_push($productsIdsList, $item->getProduct()->getSku());
            array_push($productsNamesList, $item->getProduct()->getName());
        }

        $grandTotal = $cart->getGrandTotal();

        $data = array(
            'date' => $dateTime->format('c'),
            'products' => implode(',', $productsIdsList),
            'contactExtEventType' => 'CART',
            'value' => $grandTotal,
            'detail1' => implode(",", $productsNamesList),
        );

        return $data;
    }

    public function setWishlistData($product){
        $dateTime = new DateTime('NOW');

        $data = array(
            'date' => $dateTime->format('c'),
            'contactExtEventType' => 'OTHER',
            'products' => $product->getSku(),
            'value' => round($product->getPrice(), 2) ,
            'detail1' => $product->getName(),
            'description' => 'WISHLIST'
        );

        return $data;
    }

    public function checkData($entity,$array = array()){
        foreach ($entity->getData() as $key => $value) {
            if (!$entity->dataHasChangedFor($key)) continue;
            if(!in_array($key,$array)) continue;
            return true;
        }
        return false;
    }

    public function checkEqualData($entity,$model){
        if($entity->isObjectNew()){
           return false;
        }

        $entityLoad = Mage::getSingleton($model)->load($entity->getId());
        if($entityLoad->getData() == $entity->getData()){
            return true;
        }
        return false;
    }

    public function addTags($tags,$data = null){
        if($tags != ''){
            if(isset($data)){
                $data = array_merge($data,explode(",", $tags));
            }else{
                $data = explode(",", $tags);
            }
        }

        return $data;
    }

    public function addOpt($data,$opt){
        if($opt){
            $data['forceOptIn'] = true;
            $data['forceOptOut'] = false;
        }else{
            $data['forceOptIn'] = false;
            $data['forceOptOut'] = true;
        }

        return $data;
    }

    public function setCookie($smCookie = null){
        if (!isset($_COOKIE['smclient']) || empty($_COOKIE['smclient'])) {
            $period = time() + 36500 * 86400;
            if($smCookie==null && Mage::getSingleton('customer/session')->isLoggedIn()){
                $customerData = Mage::getSingleton('customer/session')->getCustomer();
                $contactId = $customerData->getSalesmanagoContactId();
                $cookie = Mage::getSingleton('core/cookie');
                $cookie->set('smclient', $contactId ,$period,'/');
            }elseif($smCookie!=null){
                $cookie = Mage::getSingleton('core/cookie');
                $cookie->set('smclient', $smCookie ,$period,'/');
            }
        }
    }

    public function salesmanagoSync($data,$url,$async=false){
        $requestTime = time();
        $apiKey = md5($requestTime . $this->getApiSecret());

        $data_to_json = array(
            'apiKey' => $apiKey,
            'clientId' => $this->getClientId(),
            'requestTime' => $requestTime,
            'sha' => sha1($apiKey . $this->getClientId() . $this->getApiSecret()),
            'owner' => $this->getOwnerEmail(),
            'async' => $async
        );

        $data_to_json = array_merge($data_to_json,$data);

        $json = json_encode($data_to_json);
        $result = $this->_doPostRequest('https://' . $this->getEndPoint() . '/'.$url, $json);

        return json_decode($result, true);
    }

    public function _doPostRequest($url, $data) {
        $debugMode  = Mage::getStoreConfig('devopensalesmanago/debug/active_debug');

        if($debugMode){
            Mage::log($data, null, 'SM_debug_data_sales_manago.log');
            Mage::log($url, null, 'SM_debug_data_sales_manago.log');
            Mage::log(json_decode($data), null, 'SM_debug_data_sales_manago.log');
        }

        $connection_timeout = Mage::getStoreConfig('devopensalesmanago/general/connection_timeout');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(isset($connection_timeout) && !empty($connection_timeout)){
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $connection_timeout);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));

        $result = curl_exec($ch);

        if(curl_errno($ch) > 0){
            if(curl_errno($ch)==28){
                Mage::log("TIMEOUT ERROR NO: " . curl_errno($ch));
            } else{
                Mage::log("ERROR NO: " . curl_errno($ch));
            }
            return false;
        }

        if($debugMode){
            Mage::log($result, null, 'SM_debug_result_sales_manago.log');
        }

        return $result;
    }

    public function genXlsOrders($orderIds, $year = null){

        if($year != null){
            $orders = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('created_at', array(
                'from'  => $year.'-01-01',
                'to'    => $year.'-12-31',
                'date'  => true,
            ));
        }else{
            $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $orderIds ));
        }


        function xlsBOF() {
            echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
        }
        function xlsEOF() {
            echo pack("ss", 0x0A, 0x00);
        }
        function xlsWriteNumber($Row, $Col, $Value) {
            echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
            echo pack("d", $Value);
        }
        function xlsWriteLabel($Row, $Col, $Value) {
            $L = strlen($Value);
            echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
            echo $Value;
        }

        // prepare headers information
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename=\"export_orders_sm_".date("Y-m-d").".xls\"");
        header("Content-Transfer-Encoding: binary");
        header("Pragma: no-cache");
        header("Expires: 0");

        // start exporting
        xlsBOF();

        // first row
        xlsWriteLabel(0, 0, utf8_decode("email"));
        xlsWriteLabel(0, 1, utf8_decode("products"));
        xlsWriteLabel(0, 2, utf8_decode("total_amount"));
        xlsWriteLabel(0, 3, utf8_decode("order_date"));

        Mage::log(count($orders), null, 'count.log');

        $i=0; //filas
        foreach($orders as $_order) {
            $i++;

            $productsIdsList    = array();
            $items              = $_order->getAllVisibleItems();

            foreach ($items as $item) {
                array_push($productsIdsList, $item->getProduct()->getSku());
            }

            xlsWriteLabel($i, 0, $_order->getCustomerEmail());
            xlsWriteLabel($i, 1, utf8_decode(implode(',',$productsIdsList)));
            xlsWriteLabel($i, 2, utf8_decode($_order->getGrandTotal()));
            xlsWriteLabel($i, 3, utf8_decode(Mage::getModel('core/date')->date('Y-m-d H:i:s',$_order->getCreatedAt())));

        }

        xlsEOF();


    }

}