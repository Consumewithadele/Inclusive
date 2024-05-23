<?php
namespace Inclusive\WebinarsEvents\Model\Event\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Schedule extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('inclusive_events_schedule', 'date_location_id');
    }
}