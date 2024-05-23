<?php
namespace Inclusive\WebinarsEvents\Model\Event;

use Inclusive\WebinarsEvents\Model\Event\ResourceModel\Schedule as ScheduleResource;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method int getProductId()
 * @method $this setProductId($id)
 * @method string getLocation()
 * @method $this setLocation($value)
 * @method $this setDate($value)
 * @method string getType()
 * @method $this setType($value)
 * @method string getAddress()
 * @method $this setAddress($value)
 * @method int getSortOrder()
 * @method $this setSortOrder($value)
 */
class Schedule extends AbstractModel implements ExtensibleDataInterface
{
    protected $timezone;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->timezone = $timezone;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init(ScheduleResource::class);
    }

    public function getValue()
    {
        return md5($this->getId() . $this->getAddress() . $this->getDate());
    }

    public function getLabel()
    {
        $date = $this->getDate(true);
        return sprintf('%s (%s)', $this->getLocation(), $date->format('d/m/Y'));
    }

        /**
     * @param bool $asObject
     * @return \DateTime|string
     */
    public function getEndDate($asObject = false)
    {
        $date = $this->getData('end_date');

        if ($asObject) {
            $date = $this->timezone->date($date);
        }

        return $date;
    }
    
    /**
     * @param bool $asObject
     * @return \DateTime|string
     */
    public function getDate($asObject = false)
    {
        $date = $this->getData('date');

        if ($asObject) {
            $date = $this->timezone->date($date);
        }

        return $date;
    }
}