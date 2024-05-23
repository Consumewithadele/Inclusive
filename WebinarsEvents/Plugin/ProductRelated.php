<?php
namespace Inclusive\WebinarsEvents\Plugin;


use Inclusive\WebinarsEvents\Helper\Event;
use Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule as ScheduleResource;
use Inclusive\WebinarsEvents\Model\Event\Schedule;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManagerInterface;

class ProductRelated
{
    /**
     * @var Event
     */
    protected $eventHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Event $eventHelper
    ) {
        $this->objectManager = $objectManager;
        $this->eventHelper = $eventHelper;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $result
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterGetUpSellProductCollection(
        \Magento\Catalog\Api\Data\ProductInterface $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $result)
    {
        $result->addFieldToFilter('type_id', ['nin' => ['webinar', 'event']]);

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $result
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterGetRelatedProductCollection(
        \Magento\Catalog\Api\Data\ProductInterface $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $result)
    {
        $result->addFieldToFilter('type_id', ['nin' => ['webinar', 'event']]);

        return $result;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $result
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function afterGetCrossSellProductCollection(
        \Magento\Catalog\Api\Data\ProductInterface $subject,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $result)
    {
        $result->addFieldToFilter('type_id', ['nin' => ['webinar', 'event']]);

        return $result;
    }
}