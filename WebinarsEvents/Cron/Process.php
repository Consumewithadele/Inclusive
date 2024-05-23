<?php

namespace Inclusive\WebinarsEvents\Cron;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct as CoreListProduct;
use Magento\Catalog\Model\Product;
use Inclusive\WebinarsEvents\Helper\Event;

class Process
{	
	protected $logger;
	public $eventHelper;
    public function __construct(
		LoggerInterface $logger,
		\Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Event $eventHelper,
        array $data = []
		) {
		$this->logger = $logger;
		$this->eventHelper = $eventHelper;
    }

    public function execute() {
		//$products = $this->eventHelper->_getProductCollection();

		$this->logger->info('Cron Works');
		
    }
}