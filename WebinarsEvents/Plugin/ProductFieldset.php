<?php
namespace Inclusive\WebinarsEvents\Plugin;

use Inclusive\WebinarsEvents\Model\Product\Type\EventOrWebinar;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;
USE Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class ProductFieldset
{
    private $webinarOrEventAttributeSetId;

    /**
     * @var AttributeSetCollection
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var ProductResource
     */
    protected $productResource;

    public function __construct(
        AttributeSetCollection $attributeSetCollection,
        ProductResource $productResource
    ) {
        $this->attributeSetCollectionFactory = $attributeSetCollection;
        $this->productResource = $productResource;
    }

    public function beforeGetAttributeSetId(Product $product)
    {
        if (in_array($product->getTypeId(), ['webinar', 'event'])
            && $this->getWebinarOrEventAttributeSetId()) {
            return $product->setData($product::ATTRIBUTE_SET_ID, $this->getWebinarOrEventAttributeSetId());
        }

        return $product;
    }

    /**
     * @return null|int
     */
    private function getWebinarOrEventAttributeSetId()
    {
        if ($this->webinarOrEventAttributeSetId === null) {
            $collection = $this->attributeSetCollectionFactory->create();
            $collection->setEntityTypeFilter($this->productResource->getTypeId())
                ->addFieldToFilter('attribute_set_name', EventOrWebinar::ATTRIBUTE_SET_NAME);


            $attributeSetId = 0;
            foreach($collection as $attr) {
                $attributeSetId = $attr->getAttributeSetId();
            }

            $this->webinarOrEventAttributeSetId = $attributeSetId;
        }


        return $this->webinarOrEventAttributeSetId;
    }
}