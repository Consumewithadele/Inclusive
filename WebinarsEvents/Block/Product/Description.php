<?php

namespace Inclusive\WebinarsEvents\Block\Product;

class Description extends AbstractProduct
{
    protected $_template = 'product/event/description.phtml';

    public function getProductDescription()
    {
        return $this->getProduct()->getData('description');
    }
}