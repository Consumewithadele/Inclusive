<?php

namespace Inclusive\WebinarsEvents\Block\Category\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct as CoreListProduct;
use Magento\Catalog\Model\Product;
use Inclusive\WebinarsEvents\Helper\Event;

class ListProduct extends CoreListProduct {

        /**
     * @var Event
     */
    protected $eventHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Event $eventHelper,
        array $data = []
    )
    {
        $this->eventHelper = $eventHelper;

        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

      /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection() {

        $collection = $this->_getProductCollection();

        $categoryId = $this->getLayer()->getCurrentCategory()->getId();
        foreach ($collection as $product) {
            $product->setData('category_id', $categoryId);
        }

        return $this->eventHelper->filterOutPastDates($collection);
    }
}