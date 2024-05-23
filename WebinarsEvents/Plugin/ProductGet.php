<?php

namespace Inclusive\WebinarsEvents\Plugin;

use Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule\CollectionFactory;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Inclusive\WebinarsEvents\Helper\Event;

class ProductGet
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @var Event
     */
    protected $eventHelper;

    /**
     * ProductGet constructor.
     * @param ProductExtensionFactory $productExtensionFactory
     * @param CollectionFactory $collectionFactory
     * @param Event $eventHelper
     */
    public function __construct(
        ProductExtensionFactory $productExtensionFactory,
        CollectionFactory $collectionFactory,
        Event $eventHelper
    )
    {
        $this->productExtensionFactory = $productExtensionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->eventHelper = $eventHelper;
    }

    public function afterLoad(ProductModel $product)
    {
        if (!$this->eventHelper->isEvent($product)) {
            return $product;
        }

        $productExtension = $product->getExtensionAttributes();
        if (null === $productExtension) {
            $productExtension = $this->productExtensionFactory->create();
        }

        $scheduleCollection = $this->collectionFactory->create();
        $scheduleCollection->addProductFilter($product);

        $items = $scheduleCollection->getItems();
        if (!empty($items)) {
            $productExtension->setEventSchedule($items);
            $product->setExtensionAttributes($productExtension);
            $product->setEventSchedule($items);
        }

        return $product;
    }
}