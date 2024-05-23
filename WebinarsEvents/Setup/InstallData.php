<?php

namespace Inclusive\WebinarsEvents\Setup;

use Inclusive\WebinarsEvents\Model\Product\Type\EventOrWebinar;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set as SetResource;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory
     */
    private $attributeFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSetInterfaceFactory
     */
    private $attributeSetFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory
     */
    private $attributeGroupFactory;

    /**
     * @var \Magento\Catalog\Api\AttributeSetManagementInterface
     */
    private $attributeSetManagement;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface
     */
    private $attributeGroupRepository;

    /**
     * AttributeSetsFixture constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Api\AttributeSetManagementInterface $attributeSetManagement
     * @param \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface $attributeGroupRepository
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Api\ProductAttributeManagementInterface $attributeManagement
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory $attributeFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     * @param \Magento\Eav\Api\Data\AttributeSetInterfaceFactory $attributeSetFactory
     * @param \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory $attributeGroupFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Catalog\Api\AttributeSetManagementInterface $attributeSetManagement,
        \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface $attributeGroupRepository,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Api\ProductAttributeManagementInterface $attributeManagement,
        \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory $attributeFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Api\Data\AttributeSetInterfaceFactory $attributeSetFactory,
        \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory $attributeGroupFactory
    )
    {
        $this->objectManager = $objectManager;
        $this->attributeRepository = $attributeRepository;
        $this->attributeManagement = $attributeManagement;
        $this->attributeFactory = $attributeFactory;
        $this->optionFactory = $optionFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /** @var Type $entityType */
        $entityType = $this->objectManager->create(Type::class)
            ->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE);

        $attributeSet = $this->getAttributeSetFromName(EventOrWebinar::ATTRIBUTE_SET_NAME, $entityType->getEntityTypeId());
        if ($attributeSet->getId()) {
            $attributeSet->delete();
        }

        /** @var Set $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setAttributeSetName(EventOrWebinar::ATTRIBUTE_SET_NAME);
        $attributeSet->setEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $attributeSet = $this->attributeSetManagement->create($attributeSet, $entityType->getId());

        $attributeSetId = $attributeSet->getAttributeSetId();
        $this->removeAttributeFromSet($attributeSetId, 'weight');
        $this->removeAttributeFromSet($attributeSetId, 'weight_type');
        $this->removeAttributeFromSet($attributeSetId, 'news_from_date');
        $this->removeAttributeFromSet($attributeSetId, 'news_to_date');
        $this->removeAttributeFromSet($attributeSetId, 'country_of_manufacture');
        $this->removeAttributeFromSet($attributeSetId, 'links_purchased_separately');
        $this->removeAttributeFromSet($attributeSetId, 'links_title');
        $this->removeAttributeFromSet($attributeSetId, 'links_exist');
        $this->removeAttributeFromSet($attributeSetId, 'shipment_type');


        $attributeGroup = $this->getAttributeGroup('Banner Config', $attributeSetId);
        if ($attributeGroup->getId()) {
            $attributeGroup->delete();
        }

      /** @var \Magento\Eav\Api\Data\AttributeGroupInterface $attributeGroup */
        $attributeGroup = $this->attributeGroupFactory->create();
        $attributeGroup->setAttributeGroupName('Banner Config');
        $attributeGroup->setAttributeSetId($attributeSetId);
        $this->attributeGroupRepository->save($attributeGroup);
        $attributeGroupId = $attributeGroup->getAttributeGroupId();


        $attributesData = [
            [
                'is_required' => 0,
                'is_visible_on_front' => 1,
                'is_visible_in_advanced_search' => 0,
                'attribute_code' => 'banner_image',
                'is_searchable' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0,
                'frontend_label' => 'Banner Image',
                'frontend_input' => 'media_image',
                'frontend_model' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
            ],
            [
                'is_required' => 0,
                'is_visible_on_front' => 1,
                'is_visible_in_advanced_search' => 0,
                'attribute_code' => 'banner_title',
                'is_searchable' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0,
                'frontend_label' => 'Banner Title',
                'frontend_input' => 'text',
            ],
            [
                'is_required' => 0,
                'is_visible_on_front' => 1,
                'is_visible_in_advanced_search' => 0,
                'attribute_code' => 'banner_posted_date',
                'is_searchable' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0,
                'frontend_label' => 'Banner Posted Date',
                'frontend_input' => 'date',
            ],
        ];

        $sortOrder = 100;
        foreach ($attributesData as $attributeData) {
            try {
                /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
                $attribute = $this->attributeRepository->get($attributeData['attribute_code']);
                if ($attribute->getId()) {
                    $attribute->delete();
                }
            } catch (NoSuchEntityException $e) {}
            /** @var ProductAttributeInterface $attribute */
            $attribute = $this->attributeFactory->create(['data' => $attributeData]);

            $productAttribute = $this->attributeRepository->save($attribute);
            $attributeId = $productAttribute->getAttributeId();

            //Associate Attribute to Attribute Set
            $this->attributeManagement->assign($attributeSetId, $attributeGroupId, $attributeId, $sortOrder);
            $sortOrder += 10;
        }

        $installer->endSetup();
    }

    private function removeAttributeFromSet($attributeSetId, $attributeCode)
    {
        try {
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            $attribute = $this->attributeRepository->get($attributeCode);
        } catch (NoSuchEntityException $e) {
            return;
        }

        // Check if attribute is in set
        $attribute->setAttributeSetId($attributeSetId);
        $attribute->loadEntityAttributeIdBySet();

        if ($attribute->getEntityAttributeId()) {
            $attribute->deleteEntity();
        }
    }

    /**
     * @param $name
     * @return Set
     */
    private function getAttributeSetFromName($name, $typeId)
    {
        /** @var Set $set */
        $set = $this->attributeSetFactory->create();
        $collection = $set->getCollection()
            ->addFieldToFilter('attribute_set_name', $name)
            ->addFieldToFilter('entity_type_id', $typeId);

        return $collection->getFirstItem();
    }

    /**
     * @param $name
     * @param $setId
     * @return Group
     */
    public function getAttributeGroup($name, $setId)
    {
        /** @var Group $attributeGroup */
        $attributeGroup = $this->attributeGroupFactory->create();
        $collection = $attributeGroup->getCollection();
        $collection
            ->addFieldToFilter('attribute_group_name', $name)
            ->addFieldToFilter('attribute_set_id', $setId);

        return $collection->getFirstItem();
    }
}
