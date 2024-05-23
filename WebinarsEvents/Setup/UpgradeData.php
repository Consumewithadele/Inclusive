<?php

namespace Inclusive\WebinarsEvents\Setup;

use Inclusive\WebinarsEvents\Model\Product\Type\EventOrWebinar;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use \Magento\Eav\Api\Data\AttributeSetInterfaceFactory;
use \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use \Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use \Magento\Catalog\Api\ProductAttributeManagementInterface;

class UpgradeData implements UpgradeDataInterface {

    private $_eav_setup_factory;
    private $_attribute_set_factory;
    private $_attribute_group_factory;
    private $_attribute_group_repository;
    private $_attribute_factory;
    private $_attribute_repository;
    private $_attribute_management;

    public function __construct(
        AttributeSetInterfaceFactory $attribute_set_factory,
        AttributeGroupInterfaceFactory $attribute_group_factory,
        ProductAttributeGroupRepositoryInterface $attribute_group_repository,
        ProductAttributeInterfaceFactory $attribute_factory,
        ProductAttributeRepositoryInterface $attribute_repository,
        ProductAttributeManagementInterface $attribute_management
    ) {
        $this->_attribute_set_factory = $attribute_set_factory;
        $this->_attribute_group_factory = $attribute_group_factory;
        $this->_attribute_group_repository = $attribute_group_repository;
        $this->_attribute_factory = $attribute_factory;
        $this->_attribute_repository = $attribute_repository;
        $this->_attribute_management = $attribute_management;
    }

	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        if(version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addEventListingAttributes();
        }
    }

    private function addEventListingAttributes() {
        // Get attribute set data from ATTRIBUTE_SET_NAME
        $attribute_set_factory = $this->_attribute_set_factory->create();
        $attribute_set_collection = $attribute_set_factory->getCollection()->addFieldToFilter('attribute_set_name', EventOrWebinar::ATTRIBUTE_SET_NAME);
        $attribute_set = $attribute_set_collection->getFirstItem();
        $attribute_set_id = $attribute_set->getAttributeSetId();

        // Create new attribute group
        $attribute_group = $this->_attribute_group_factory->create();
        $attribute_group->setAttributeGroupName('Event Listings');
        $attribute_group->setAttributeSetId($attribute_set_id);
        $attribute_group = $this->_attribute_group_repository->save($attribute_group);
        $attribute_group_id = $attribute_group->getAttributeGroupId();

        // Create 'List Item Text' attribute
        $list_item_text_attribute = $this->_attribute_factory->create(['data' => [
            'is_required' => 0,
            'is_visible_on_front' => 0,
            'is_visible_in_advanced_search' => 0,
            'attribute_code' => 'event_list_item_text',
            'is_searchable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'frontend_label' => 'List Item Text',
            'frontend_input' => 'text',
        ]]);
        $list_item_text_attribute = $this->_attribute_repository->save($list_item_text_attribute);
        $this->_attribute_management->assign($attribute_set_id, $attribute_group_id, $list_item_text_attribute->getAttributeId(), 100);

        // Create 'List Item Text Colour (hex)' attribute
        $list_item_text_colour_attribute = $this->_attribute_factory->create(['data' => [
            'is_required' => 0,
            'is_visible_on_front' => 0,
            'is_visible_in_advanced_search' => 0,
            'attribute_code' => 'event_list_item_text_colour',
            'is_searchable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'frontend_label' => 'List Item Text Colour (hex)',
            'frontend_input' => 'text',
        ]]);
        $list_item_text_colour_attribute = $this->_attribute_repository->save($list_item_text_colour_attribute);
        $this->_attribute_management->assign($attribute_set_id, $attribute_group_id, $list_item_text_colour_attribute->getAttributeId(), 150);
    }

}