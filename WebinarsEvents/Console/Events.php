<?php
namespace Inclusive\WebinarsEvents\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct as CoreListProduct;
use Magento\Catalog\Model\Product;
use Inclusive\WebinarsEvents\Helper\Event;


class Events extends Command
{
    
    protected $_state;
    public function __construct() {
        return parent::__construct();
    }

   protected function configure()
   {
       $this->setName('events:clean');
       $this->setDescription('Demo command line');
       
       parent::configure();
   }
   
   protected function execute(InputInterface $input, OutputInterface $output)
   {
    $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
    $DateNow = new \DateTime();
    $DateNow = new \DateTime($DateNow->format('l d M Y'));

    $this->_state = $objectManager->get('\Magento\Framework\App\State');
    $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
    $categoryHelper = $objectManager->get('\Magento\Catalog\Helper\Category');
    $categoryRepository = $objectManager->get('\Magento\Catalog\Model\CategoryRepository');
    $stores = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStores();
    $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
    $categoryId = 3; // YOUR CATEGORY ID
    $category = $categoryFactory->create()->load($categoryId);

    $categoryProducts = $category->getProductCollection()
                                ->addAttributeToSelect('*');

    $output->writeln("Checking Events for expired events");            
    $output->writeln('<fg=cyan>'.count($categoryProducts).' Events found</>');

    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection = $resource->getConnection();
    $tableName = $resource->getTableName('inclusive_events_schedule');
    $productsUnpublished = 0;
    $unpublishArray = [];
    foreach ($categoryProducts as $product){
        $prod = $product->getData();
        $output->writeln(PHP_EOL . PHP_EOL.'<fg=green>'.$product['name'].'</>' . PHP_EOL);
        $sql = "Select * FROM " . $tableName . " WHERE product_id = '".$prod['entity_id']."'";
        $result = $connection->fetchAll($sql);
        $unpublishProduct = true;
        $lastDate = false;
        //if result empty dont unpublish
        $output->writeln('<fg=cyan>'.count($result).' Events Found</>');

        if(count($result) == 1){
            //if there is only one event
            if(strtotime($result[0]['end_date']) > time()){
                $unpublishProduct = false;
                //event has not passed so dont unpublish the product
            }
        }else{
            //multiple results
            foreach($result as $event){
                $finalDate = $event['end_date'];
                if($lastDate == false){
                    $lastDate = $finalDate;
                    if(strtotime($finalDate) > time()){
                        $unpublishProduct = false;
                        $output->writeln('<fg=yellow>Date Not Passed</>');
                    }
                }else{
                    if(strtotime($finalDate) > $lastDate){
                        $lastDate = $finalDate;
                        if(strtotime($finalDate) > time()){
                            $unpublishProduct = false; 
                            $output->writeln('<fg=yellow>Date Not Passed</>');
                        }
                        
                        
                    }
                }
            }
        }

        if($unpublishProduct){
            $productsUnpublished++;
            
            array_push($unpublishArray,$prod['entity_id']);

            // Disable product for default store view
            $product->setStoreId(0);
            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
            $product->save();

            // Disable product for all other store views
            foreach($stores as $storeId => $store) {
              $product->setStoreId($storeId);
              $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
              $product->save();
            }
        }

    }     
    $output->writeln('<fg=cyan>'.$productsUnpublished.' Events Unpublished</>');
   }
}
