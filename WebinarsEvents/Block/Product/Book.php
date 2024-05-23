<?php

namespace Inclusive\WebinarsEvents\Block\Product;

use Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule\CollectionFactory;

class Book extends AbstractProduct
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    protected $_template = 'product/event/book.phtml';

    /**
     * @var \Inclusive\WebformEventField\Helper
     */
    private $_WebinarEventFieldCommon;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Inclusive\WebformEventField\Helper\Common $WebinarEventFieldCommon,
        array $data = []
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->_WebinarEventFieldCommon = $WebinarEventFieldCommon;

        parent::__construct($context, $registry, $storeManager, $imageHelper, $timezone, $data);
    }

    /**
     * @return string
     */
    public function getBookUrl()
    {
        return $this->getUrl("webinars-and-events/request/book");
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSchedule()
    {
        return $this->getProduct()->getData('event_schedule');
    }

    /**
     * returns the webinar webform id saved in the core_config_data table
     */
    public function getWebinarWebformId() {
        return $this->_WebinarEventFieldCommon->getWebinarWebformId();
    }
}