<?php

namespace Inclusive\WebinarsEvents\Model;

use Magento\Framework\Event\ObserverInterface;

class Observer implements ObserverInterface
{
    /**
     * @var \Inclusive\WebinarsEvents\Helper\Event
     */
    protected $helper;

    /**
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     */
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Inclusive\WebinarsEvents\Helper\Event $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * Append review summary before rendering html
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection instanceof \Magento\Framework\Data\Collection) {
            if ($this->helper->isEventCategory($productCollection)) {
                $productCollection->setPageSize(0);
            }
        }
    }
}