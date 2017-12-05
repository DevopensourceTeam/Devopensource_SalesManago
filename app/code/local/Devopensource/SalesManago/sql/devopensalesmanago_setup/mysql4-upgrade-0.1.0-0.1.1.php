<?php
/**
 * Created by PhpStorm.
 * User: marc
 * Date: 30/03/15
 * Time: 13:15
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'sm_tag_navigation', array(
    'group'         => 'Sales Manago',
    'input'         => 'multiselect',
    'type'          => 'varchar',
    'label'         => 'SM tags',
    'backend'       => 'eav/entity_attribute_backend_array',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$installer->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'sm_tag_navigation', array(
    'group'         => 'Sales Manago',
    'input'         => 'multiselect',
    'type'          => 'varchar',
    'label'         => 'Tag',
    'source'		=> 'devopensalesmanago/source_tags',
    'backend'       => 'eav/entity_attribute_backend_array',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$installer->endSetup();


