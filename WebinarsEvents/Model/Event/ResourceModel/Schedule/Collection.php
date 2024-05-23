<?php
namespace Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Inclusive\WebinarsEvents\Model\Event\Schedule::class,
            \Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule::class
        );
    }

    public function addProductFilter($product)
    {
        if ($product instanceof ProductModel) {
            $product = $product->getId();
        }

        if (is_array($product)) {
            $this->addFieldToFilter('product_id', array('in' => $product));
        } else {
            $this->addFieldToFilter('product_id', $product);
        }

        $this->setOrder('sort_order', 'ASC');

        return $this;
    }
}