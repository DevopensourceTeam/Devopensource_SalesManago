<?php
/**
 * Created by PhpStorm.
 * User: marc
 * Date: 20/1/17
 * Time: 16:04
 */

class Devopensource_SalesManago_Model_Source_Tags extends Mage_Eav_Model_Entity_Attribute_Source_Table
{

    public function getAllOptions(){
        $attribute = Mage::getModel('catalog/entity_attribute')
            ->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'sm_tag_navigation');
        if ( ! $attribute->getSourceModel()) {
            $attribute->setSourceModel('eav/entity_attribute_source_table');
        }
        return  $attribute->getSource()->getAllOptions();
    }

    public function getOptionText($optionId){
        $attribute = Mage::getModel('catalog/entity_attribute')
            ->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'sm_tag_navigation');
        if ( ! $attribute->getSourceModel()) {
            $attribute->setSourceModel('eav/entity_attribute_source_table');
        }

        return $attribute->getSource()->getOptionText($optionId);
    }


}