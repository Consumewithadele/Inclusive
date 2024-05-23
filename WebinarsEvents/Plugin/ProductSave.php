<?php
namespace Inclusive\WebinarsEvents\Plugin;


use Inclusive\WebinarsEvents\Helper\Event;
use Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule as ScheduleResource;
use Inclusive\WebinarsEvents\Model\Event\Schedule;
use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManagerInterface;

class ProductSave
{
    /**
     * @var ScheduleResource
     */
    protected $scheduleResource;

    /**
     * @var Event
     */
    protected $eventHelper;

    /**
     * @var ScheduleCollectionFactory
     */
    protected $scheduleCollectionFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ScheduleResource $scheduleResource,
        Event $eventHelper,
        ScheduleCollectionFactory $scheduleCollectionFactory
    ) {
        $this->objectManager = $objectManager;
        $this->scheduleResource = $scheduleResource;
        $this->eventHelper = $eventHelper;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
    }

    /**
     * @param Product $subject
     * @return Product
     */
    public function afterSave(Product $subject)
    {
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->addProductFilter($subject);
        $schedules = $scheduleCollection->getItems();
        $postedScheduleIds = [];

        if ($this->eventHelper->isEvent($subject) && $subject->getData('schedules')) {
            foreach ($subject->getData('schedules') as $data) {
                if (isset($data['date_location_id']) && $data['date_location_id']) {
                    array_push($postedScheduleIds, $data['date_location_id']);
                    $schedule = $this->getScheduleInstance();
                    $schedule->load($data['date_location_id']);

                    if ($schedule->getProductId() != $subject->getId()) {
                        continue;
                    }
                } else {
                    /** @var Schedule $schedule */
                    $schedule = $this->getScheduleInstance();
                }

                $schedule
                    ->setProductId($subject->getId())
                    ->setLocation($data['location'])
                    ->setDate($data['date'])
                    ->setEndDate($data['end_date'])
                    ->setType($data['type'])
                    ->setSortOrder($data['sort_order'])
                    ->setAddress($data['address']);

                $this->scheduleResource->save($schedule);
            }
        }

        // Remove schedules
        foreach($schedules as $schedule) {
          if(!in_array($schedule->getId(), $postedScheduleIds)) {
            $this->scheduleResource->delete($schedule);
          }
        }

        return $subject;
    }

    /**
     * @return Schedule $schedule
     */
    public function getScheduleInstance()
    {
        return $this->objectManager->create(Schedule::class);
    }
}