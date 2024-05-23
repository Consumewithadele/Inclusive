<?php

namespace Inclusive\WebinarsEvents\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

class AbstractProduct extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Product
     */
    protected $product;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = []
    )
    {
        $this->timezone = $timezone;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return Product
     * @throws LocalizedException
     */
    public function getProduct()
    {
        if (is_null($this->product)) {
            $this->product = $this->registry->registry('product');

            if (!$this->product->getId()) {
                throw new LocalizedException(__('Failed to initialize product'));
            }
        }

        return $this->product;
    }
}