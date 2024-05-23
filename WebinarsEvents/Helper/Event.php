<?php

namespace Inclusive\WebinarsEvents\Helper;

use DateTime;
use Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule\CollectionFactory;
use Inclusive\WebinarsEvents\Model\Event\Schedule;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Event extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolve;

     /**
     * Holds the already filterd product
     */
    protected $FilteredProduct;

        /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $isEventCategory;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolve,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->layerResolve = $layerResolve;
        $this->date = $date;

        parent::__construct($context);
    }

    public function isEvent(ProductInterface $product)
    {
        if (in_array($product->getTypeId(), ['webinar', 'event'])) {
            return true;
        }

        return false;
    }

    
    public function getSpecificNextEventDate(ProductInterface $product,$eventId)
    {

        $schedule = $product->getEventSchedule();
        $date = '';
        if ($this->isEvent($product)) {
            if (empty($schedule) || !is_array($schedule)) {
                $scheduleCollection = $this->collectionFactory->create();
                $scheduleCollection->addProductFilter($product);

                $schedule = $scheduleCollection->getItems();
            }

            if (!empty($schedule)) {
                $result = null;

                foreach ($schedule as $value) {
                    if($value->getDateLocationId() == $eventId)
                    {
                        $result = $value->getDate(true);
                    }
                }

                if ($result instanceof \DateTime) {
                    $date = $result->format('l d M Y');
                }
            }
        }

        return $date;

    }

    public function getNextEventDate(ProductInterface $product, $returnString = true)
    {
        $schedule = $product->getEventSchedule();
        if ($this->isEvent($product)) {
            if (empty($schedule) || !is_array($schedule)) {
                $scheduleCollection = $this->collectionFactory->create();
                $scheduleCollection->addProductFilter($product);

                $schedule = $scheduleCollection->getItems();
            }

            if (!empty($schedule)) {
                $result = null;

                foreach ($schedule as $value) {
                    /** @var Schedule $value */

                    $date = $value->getDate(true);

                    if($this->dateHasPassed($date)) {
                        continue;
                    }

                    if(!$result) {
                        $result = $date;

                        continue;
                    }
                        
                    if($result > $date) {
                        $result = $date;
                    }
                }

                if(
                    $returnString &&
                    is_object($result) &&
                    $result instanceof DateTime
                ) {
                    return $result->format('l d M Y');
                }

                return $result;
            }
        }

        return null;
    }

    protected function dateHasPassed($date): bool
    {
        $midnightToday = new DateTime('midnight');

        if(
            is_object($date) &&
            $date instanceof DateTime &&
            $date < $midnightToday
        ) {
            return true;
        }

        if(
            gettype($date) == 'string' &&
            new DateTime($date) < $midnightToday
        ) {
            return true;
        }

        return false;
    }



    /**
     * Hide toolbar on event pages
     *
     * @param \Magento\Framework\Data\Collection $productCollection
     * @return bool
     */
    public function showToolbar(\Magento\Framework\Data\Collection $productCollection)
    {
        return !$this->isEventCategory($productCollection);
    }

    /**
     * Hide toolbar on event pages
     *
     * @param \Magento\Framework\Data\Collection $productCollection
     * @return bool
     */
    public function showCallToAction(\Magento\Framework\Data\Collection $productCollection)
    {
        return !$this->isEventCategory($productCollection);
    }


    /**
     * @param \Magento\Framework\Data\Collection $productCollection
     * @return bool
     */
    public function isEventCategory(\Magento\Framework\Data\Collection $productCollection = null)
    {
        if (!isset($this->isEventCategory)) {

            if (!isset($productCollection)) {
                $TempCollection = $this->_getProductCollection();
            } else {
                $TempCollection = clone $productCollection;
            }

            $first = $TempCollection->getFirstItem();
            if ($first instanceof ProductInterface) {
                $this->isEventCategory = $this->isEvent($first);
            }
            
            //$this->isEventCategory = $this->filterOutPastDates($this->isEventCategory);
        }

        return $this->isEventCategory;
    }


    /**
     * Check if the given product had event in the future.
     */
    public function hasAtLeastOneCurrentOrFutureEvent(ProductInterface $product) {
        $DateNow = new \DateTime();
        $DateNow = new \DateTime($DateNow->format('l d M Y'));
        $schedule = $product->getEventSchedule();

        if ($this->isEvent($product)) {
            if (empty($schedule) || !is_array($schedule)) {
                $scheduleCollection = $this->collectionFactory->create();
                $scheduleCollection->addProductFilter($product);

                $schedule = $scheduleCollection->getItems();
            }

            if (!empty($schedule)) {

                foreach ($schedule as $value) {
                    $StartDate = $value->getDate(true);
                    $EndDate = $value->getEndDate(true);

                    if( (isset($StartDate) && isset($StartDate)) && ($StartDate >= $DateNow || $EndDate >= $DateNow) ) {
                        // This event is still valid
                        return true;
                    }

                }

            }
        }

        return false;
    }


    public function filterOutPastDates($productCollection) {

        if ($this->FilteredProduct === null && $this->_getLayer()) {

            if($this->isEventCategory($productCollection)) {
                // only run the filter if the products are of event types
                foreach($productCollection as $Product) {
                    if(!$this->hasAtLeastOneCurrentOrFutureEvent($Product)) {
                        // remove from list
                        $productCollection->removeItemByKey($Product->getId());
    
                    }
                }
            }

            $this->FilteredProduct = $productCollection;
        }

        return $this->FilteredProduct;
    }
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    protected function _getProductCollection()
    {
        if ($this->productCollection === null && $this->_getLayer()) {
            $this->productCollection = $this->_getLayer()->getProductCollection();
        }

        return $this->productCollection;
    }

    /**
     * @return \Magento\Catalog\Model\Layer
     */
    protected function _getLayer()
    {
        return $this->layerResolve->get();
    }
    
}