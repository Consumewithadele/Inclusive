<?php

namespace Inclusive\WebinarsEvents\Plugin\Mostviewed;

class ExcludeEvents
{
    /**
     * @var \Amasty\Mostviewed\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var mixed
     */
    protected $_currentProduct;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    public function __construct(
        \Amasty\Mostviewed\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->_catalogConfig = $catalogConfig;
        $this->_currentProduct = $this->_registry->registry('current_product');
        $this->_checkoutSession = $session;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    public function beforeItemsCollectionModifiedByType(
        \Amasty\Mostviewed\Helper\Data $subject,
        $type,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\Config $catalogConfig = null,
        $findedItems = [],
        $excludedItems = []
    )
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('type_id', ['in' => ['webinar', 'event']]);

        $excludedItems = array_merge($excludedItems, $collection->getAllIds());

        return [
            $type,
            $product,
            $catalogConfig,
            $findedItems,
            $excludedItems
        ];
    }
}
