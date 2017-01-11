<?php
/**
 * @category Devopensource
 * @package Devopensource_SalesManago
 * @author Jose Ruzafa <jose.ruzafa@devopensource.com>
 * @version 0.1.0
 * @copyright Copyright (c) 2016 Devopensource
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Devopensource_SalesManago_Helper_Products extends Mage_Core_Helper_Abstract {

    public function exportProducts($download = false, $productsId = null)
    {
        Mage::app()->setCurrentStore('default');
        $path_import        =  Mage::getBaseDir('var') .'/export/';
        $file               = $path_import."products_salesManago.xml";



        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToSelect('entity_id');

        if(!is_null($productsId)){
            $products->addFieldToFilter('entity_id', array("in" => $productsId));
        }
        
        $prodIds=$products->getAllIds();

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // Root tag
        $productsX = $doc->createElement( "products" );
        $doc->appendChild( $productsX );

        $rewrite = Mage::getModel('core/url_rewrite');
        $params = array();
        $params['_current']     = false;

        $categoriesMap = $this->_getCategories();
        
        foreach($prodIds as $productId) {

            $_product           = Mage::getModel('catalog/product')->load($productId);
            $urlEnd             = $_product->getProductUrl();
            $arrayOfParentIds   = '';

            if($_product->getVisibility() == 1){

                $arrayOfParentIds = $this->_getGroupedParentIdsByChild($_product->getId());

                if(count($arrayOfParentIds) == 0){

                    $arrayOfParentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($_product->getId());
                }

                $parentId       = (count($arrayOfParentIds) > 0 ? $arrayOfParentIds[0] : null);
                $urlProduct     = $_product->getProductUrl();
                $idPath         = 'product/'.$parentId;
                $rewrite->loadByIdPath($idPath);
                $parentUrl      = Mage::getUrl($rewrite->getRequestPath(), $params);
                $urlEnd         = ($parentUrl ? $parentUrl : $urlProduct);
            }

            $product = $doc->createElement( "Row" );

            /*
             * SKU
             * */
            $sku = $doc->createElement( "ID" );
            $sku->appendChild(
                $doc->createTextNode( trim($_product->getSku()) )
            );
            $product->appendChild( $sku );

            /*
             * URL
             * */
            $url = $doc->createElement( "url" );
            $url->appendChild(
                $doc->createTextNode( trim( $urlEnd, ' \t\n\r\/' ) )
            );
            $product->appendChild( $url );

            /*
            * Foto
            * */
            $image = $doc->createElement( "Foto" );


            $_productParent = '';
            if(count($arrayOfParentIds) > 0
                && $_product->getVisibility() == 1){

                $_productParent = Mage::getModel('catalog/product')->load($arrayOfParentIds[0]);

                if($_productParent->getData('image')!='no_selection'){
                    $imgPath = Mage::getBaseUrl().'media/catalog/product'.$_productParent->getData('image');

                }else{
                    $imgPath= $_productParent->getData('image');
                }

            }else{
                if($_product->getData('image')!='no_selection'){
                    $imgPath = Mage::getBaseUrl().'media/catalog/product'.$_product->getData('image');
                }
                else{
                    $imgPath=$_product->getData('image');
                }

            }

            $image->appendChild(
                $doc->createTextNode( $imgPath )
            );
            $product->appendChild( $image );

            /*
             * Nombre
             * */
            $name = $doc->createElement( "Nombre" );
            $name->appendChild(
                $doc->createTextNode( trim($_product->getName()) )
            );
            $product->appendChild( $name );

            /*
            * Categoria
            * */
            $cats       = $_product->getCategoryIds();

            if($_productParent!= '' && $_productParent->getId() != ''){
                $cats       = $_productParent->getCategoryIds();
            }

            $categorie  = $doc->createElement( "Categoria" );
            $catOutPut  = '';

            foreach ($cats as $category_id) {

                if($categoriesMap[$category_id]['level'] == 2 && $categoriesMap[$category_id]['children_count'] > 0){
                    continue;
                }

                $catTxt     = implode('_',$categoriesMap[$category_id]['pathName']);
                $catOutPut .= $catTxt. ' : ';
            }

            $catOutPut = trim( $catOutPut, ' : ');

            if(count($cats) == 0){
                $categorie->appendChild(
                    $doc->createTextNode('no_category')
                );

            }else{
                $categorie->appendChild(
                    $doc->createTextNode($catOutPut)
                );

            }
            
            $product->appendChild( $categorie );

            /*
            * Precio Final
            * */
            $showPrice = $_product->getSpecialPrice();
            if($showPrice == 0 || $showPrice == '')
            {
                $showPrice = $_product->getPrice();
            }
            $price = $doc->createElement( "Precio_final" );
            //$roundPrice = round($_product->getPrice(),2);
            $price->appendChild(
                $doc->createTextNode( trim($showPrice) )
            );
            $product->appendChild( $price );

            /*
            * Cantidad
            * */
            $qty = $doc->createElement( "Cantidad" );
            $qty->appendChild(
                $doc->createTextNode(  $_product->getStockItem()->getQty() )
            );
            $product->appendChild( $qty );

            /*
            * Estado
            * */
            $statusTxt = 'no';
            
            if($_product->getStatus() == 1){

                $statusTxt = 'si';
            }

            $status = $doc->createElement( "Estado" );
            $status->appendChild(
                $doc->createTextNode( $statusTxt )
            );
            $product->appendChild( $status );

            $productsX->appendChild($product);
        }

        if (file_exists($file)) { unlink ($file); }

        file_put_contents($file,$doc->saveXML());

        if($download){
            return $file;
        }

    }

    protected function _getCategories(){

        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*');
        $categoryArray  = array();
        $excludeCat     = array(1,2);

        foreach($categories as $category){
            $categoryArray[$category->getId()]['name'] = $category->getName();
            $categoryArray[$category->getId()]['level'] = $category->getLevel();
            $categoryArray[$category->getId()]['pathIds'] = $category->getPathIds();
            $categoryArray[$category->getId()]['children_count'] = $category->getChildrenCount();
        }
        foreach($categoryArray as $index=>$category){
            foreach($category['pathIds'] as $idcat){
                if(in_array($idcat,$excludeCat)){
                    continue;
                }
                $categoryArray[$index]['pathName'][] = $categoryArray[$idcat]['name'];
            }
        }

        return $categoryArray;
    }

    protected function _getGroupedParentIdsByChild($childId)
    {
        $parentIds      = array();
        $resource       = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');

        $select = $readConnection->select()
            ->from('catalog_product_link', array('product_id', 'product_id','linked_product_id'))
            ->where('linked_product_id IN(?)', $childId);

        foreach ($readConnection->fetchAll($select) as $row) {
            $parentIds[] = $row['product_id'];
        }

        return $parentIds;
    }

}