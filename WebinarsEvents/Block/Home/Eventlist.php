<?php

namespace Inclusive\WebinarsEvents\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct as CoreListProduct;
use Magento\Catalog\Model\Product;

class Eventlist extends CoreListProduct
{
    protected $_collectionFactory;
    protected $_eventsCollectionFactory;
    protected $_storeManager;    

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule\CollectionFactory $eventsCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_eventsCollectionFactory = $eventsCollectionFactory;
        $this->_storeManager = $storeManager;

        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }


    public function getEventListCollection()
    {

        $eventsCollection = $this->_eventsCollectionFactory->create();//->addFieldToSelect('product_id'); 
        $productIds = array();
        $eventDates = array();
        $todayDate = date("Y-m-d");
        $scheduleData = array();
 
        foreach($eventsCollection as $eventData)
        {
            if(strtotime($todayDate) <= strtotime($eventData->getEndDate())) //strtotime($eventData->getDate()) <= strtotime($todayDate) && 
            {
                $eventId = $eventData->getDateLocationId();
                $proId = $eventData->getProductId();
                $productIds[] = $proId;
                $scheduleData[$proId] =  $eventId;
            }   
        } 
        
        $productCollection = $this->_collectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('type_id',array('in',['event', 'webinar']))
                        ->addAttributeToFilter('entity_id',array('in' => $productIds))
                        ->addStoreFilter($this->getStoreId())
                        ->setOrder('updated_at', 'desc')
                        ->setPageSize(5)
                        ->setCurPage(1);


        return array('collection' => $productCollection, 'schedule' => $scheduleData);
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}