<?php

namespace Inclusive\WebinarsEvents\Block\Product;

class Banner extends AbstractProduct
{
    protected $_template = 'product/event/banner.phtml';

    public function getBannerImageUrl()
    {
        if ($this->getProduct()->getData('banner_image') != '') {
            $productImage = $this->imageHelper->init($this->product, 'banner_image')
                ->setImageFile($this->getProduct()->getData('banner_image'));

            // Fix for the url
            $url = $str = str_replace('\\', '/', $productImage->getUrl());;

            return $url;
        }

        return null;
    }

    public function getBannerTitle()
    {
        if ($this->getProduct()->getData('banner_title')) {
            $title = $this->getProduct()->getData('banner_title');
        } else {
            $title = $this->getProduct()->getName();
        }

        return $title;
    }

    public function getProductPostedDate()
    {
        if ($this->getProduct()->getData('banner_posted_date')) {
            $date = $this->getProduct()->getData('banner_posted_date');
        } else {
            $date = $this->getProduct()->getCreatedAt();
        }

        $date = $this->timezone->date($date);

        return $date->format('jS F Y');
    }

    protected function _toHtml()
    {
        if ($this->getBannerImageUrl() == null) {
            return '';
        }

        return parent::_toHtml();
    }
}