<?php
namespace Inclusive\WebinarsEvents\Model\Product\Type\EventOrWebinar;

use Magento\Catalog\Model\Product;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Default action to get price of product
     *
     * @param Product $product
     * @return float
     */
    public function getPrice($product)
    {
        return 0;
    }
}
