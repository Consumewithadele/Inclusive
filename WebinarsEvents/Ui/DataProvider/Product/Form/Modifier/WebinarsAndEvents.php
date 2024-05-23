<?php

namespace Inclusive\WebinarsEvents\Ui\DataProvider\Product\Form\Modifier;

use Inclusive\WebinarsEvents\Helper\Event;
use Inclusive\WebinarsEvents\Model\Event\Schedule;
use Inclusive\WebinarsEvents\Model\Product\Type\EventOrWebinar;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Date;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;

class WebinarsAndEvents extends AbstractModifier
{
    /**
     * Group values
     */
    const GROUP_NAME               = 'event-schedule-and-locations';
    const GROUP_BANNER_CONFIG      = 'banner-config';
    const GROUP_SCOPE              = 'data.product';
    const GROUP_PREVIOUS_NAME      = 'product-details';
    const GROUP_DEFAULT_SORT_ORDER = 31;

    /**
     * Button values
     */
    const BUTTON_ADD = 'button_add';

    /**
     * Container values
     */
    const CONTAINER_HEADER_NAME         = 'container_header';
    const CONTAINER_DATES_LOCATION      = 'container_date_and_location';
    const CONTAINER_DATES_LOCATION_DATA = 'container_date_and_location_data';
    const GRID_SCHEDULE_NAME            = 'schedules';

    /**
     * Field values
     */
    const FIELD_DATE_LOCATION_ID = 'date_location_id';
    const FIELD_LOCATION_NAME    = 'location';
    const FIELD_DATE_NAME        = 'date';
    const FIELD_END_DATE_NAME    = 'end_date';
    const FIELD_TYPE_NAME        = 'type';
    const FIELD_ADDRESS_NAME     = 'address';
    const FIELD_IS_REQUIRE_NAME  = 'is_require';
    const FIELD_SORT_ORDER_NAME  = 'sort_order';
    const FIELD_IS_DELETE        = 'is_delete';

    private $webinarOrEventAttributeSet;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var AttributeSetCollection
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var Event
     */
    protected $eventHelper;

    /**
     * @var array
     */
    protected $meta = [];


    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        ProductResource $productResource,
        AttributeSetCollection $attributeSetCollection,
        Event $eventHelper
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->productResource = $productResource;
        $this->attributeSetCollectionFactory = $attributeSetCollection;
        $this->eventHelper = $eventHelper;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        if ($this->isProductWebinarOrEvent() && isset($meta['product-details']['children'])) {
            $this->meta = $this->arrayManager->remove(
                'product-details/children/container_quantity_and_stock_status',
                $this->meta
            );
            $this->meta = $this->arrayManager->remove(
                'product-details/children/quantity_and_stock_status_qty',
                $this->meta
            );


            $this->meta = array_replace_recursive(
                $this->meta,
                [
                    self::GROUP_NAME => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'label' => __('Event Schedule and locations'),
                                    'componentType' => Fieldset::NAME,
                                    'dataScope' => static::GROUP_SCOPE,
                                    'collapsible' => true,
                                    'sortOrder' => $this->getNextGroupSortOrder(
                                        $this->meta,
                                        static::GROUP_PREVIOUS_NAME,
                                        static::GROUP_DEFAULT_SORT_ORDER
                                    ),
                                ],
                            ],
                        ],
                        'children' => [
                            static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                            static::GRID_SCHEDULE_NAME => $this->getDatesGridConfig(20),
                        ],
                    ],
                ]
            );

            $this->meta = $this->arrayManager->replace(
                self::GROUP_BANNER_CONFIG.'/arguments/data/config/sortOrder',
                $this->meta,
                $this->getNextGroupSortOrder(
                    $this->meta,
                    static::GROUP_NAME,
                    static::GROUP_DEFAULT_SORT_ORDER
                )
            );

            $attributeSet = $this->getWebinarOrEventAttributeSet();
            if ($attributeSet) {
                // show only the Webinars and Events fields set
                $this->meta = $this->arrayManager->replace(
                    'product-details/children/attribute_set_id/arguments/data/config/options',
                    $this->meta,
                    [
                        [
                            'value' => $attributeSet->getAttributeSetId(),
                            'label' => $attributeSet->getAttributeSetName(),
                        ],
                    ]
                );
            }
        }

        return $this->meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if (!$this->isProductWebinarOrEvent()) {
            return $data;
        }

        // Always in stock
        $data[$this->locator->getProduct()->getId()]['product']['quantity_and_stock_status'] = [
            'is_in_stock' => 1,
            'qty' => 0,
        ];

        $schedule = $this->locator->getProduct()->getEventSchedule();
        $scheduleData = [];

        if (!empty($schedule)) {
            $index = 0;
            foreach ($schedule as $item) {
                /** @var Schedule $item */
                $scheduleData[$index] = [
                    self::FIELD_DATE_LOCATION_ID => $item->getId(),
                    self::FIELD_LOCATION_NAME => $item->getLocation(),
                    self::FIELD_DATE_NAME => $item->getDate(),
                    self::FIELD_END_DATE_NAME => $item->getEndDate(),
                    self::FIELD_TYPE_NAME => $item->getType(),
                    self::FIELD_ADDRESS_NAME => $item->getAddress(),
                    self::FIELD_SORT_ORDER_NAME => $item->getSortOrder(),
                ];
                $index++;
            }
        }

        return array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::GRID_SCHEDULE_NAME => $scheduleData,
                    ],
                ],
            ]
        );
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __(sprintf('Configure %s available locations and dates', $this->getTypeId())),
                    ],
                ],
            ],
            'children' => [
                static::BUTTON_ADD => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'title' => __('Add new location and date'),
                                'formElement' => Container::NAME,
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/form/components/button',
                                'sortOrder' => 20,
                                'actions' => [
                                    [
                                        'targetName' => 'ns = ${ $.ns }, index = ' . static::GRID_SCHEDULE_NAME,
                                        'actionName' => 'processingAddChild',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for the whole grid
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getDatesGridConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add New Date and Location'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Catalog/js/components/dynamic-rows-import-custom-options',
                        'template' => 'ui/dynamic-rows/templates/collapsible',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'addButton' => false,
                        'renderDefaultRecord' => false,
                        'columnsHeader' => false,
                        'collapsibleHeader' => true,
                        'sortOrder' => $sortOrder,
                        'imports' => ['insertData' => '${ $.provider }:${ $.dataProvider }'],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'headerLabel' => __('New Date And Location'),
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::CONTAINER_DATES_LOCATION . '.' . static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::CONTAINER_DATES_LOCATION => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Fieldset::NAME,
                                        'collapsible' => true,
                                        'label' => null,
                                        'sortOrder' => 10,
                                        'opened' => true,
                                    ],
                                ],
                            ],
                            'children' => [
                                static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(40),
                                static::CONTAINER_DATES_LOCATION_DATA => $this->getDataAndLocationRwwContainerConfig(10),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for hidden field used for sorting
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getPositionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_SORT_ORDER_NAME,
                        'dataType' => Number::NAME,
                        'visible' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];
    }

    protected function getDataAndLocationRwwContainerConfig($sortOrder)
    {
        $commonContainer = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Container::NAME,
                        'formElement' => Container::NAME,
                        'component' => 'Magento_Ui/js/form/components/group',
                        'breakLine' => false,
                        'showLabel' => false,
                        'additionalClasses' => 'admin__field-group-columns admin__control-group-equal',
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                static::FIELD_DATE_LOCATION_ID => $this->getIdFieldConfig(10),
                static::FIELD_LOCATION_NAME => $this->getLocationFieldConfig(20),
                static::FIELD_DATE_NAME => $this->getDateFieldConfig(30),
                static::FIELD_END_DATE_NAME => $this->getEndDateFieldConfig(40),
                static::FIELD_TYPE_NAME => $this->getTypeFieldConfig(50),
                static::FIELD_ADDRESS_NAME => $this->getAddressFieldConfig(60),
            ],
        ];

        return $commonContainer;
    }

    /**
     * Get config for hidden id field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getIdFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataScope' => static::FIELD_DATE_LOCATION_ID,
                        'sortOrder' => $sortOrder,
                        'visible' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for event location field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getLocationFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __(ucwords($this->getTypeId()) . ' Location'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_LOCATION_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for event location field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getDateFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __(ucwords($this->getTypeId()) . ' Date (YYYY-MM-DD)'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_DATE_NAME,
                        'dataType' => Date::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }


    protected function getEndDateFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __(ucwords($this->getTypeId()) . ' End Date (YYYY-MM-DD)'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_END_DATE_NAME,
                        'dataType' => Date::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for event type field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getTypeFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __(ucwords($this->getTypeId()) . ' Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_TYPE_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get config for event address field
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getAddressFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __(ucwords($this->getTypeId()) . ' Address'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_ADDRESS_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Check tier_price attribute scope is global
     *
     * @return bool
     */
    private function isProductWebinarOrEvent()
    {
        return $this->eventHelper->isEvent($this->locator->getProduct());
    }

    private function getTypeId()
    {
        return $this->locator->getProduct()->getTypeId();
    }

    /**
     * @return Set
     */
    private function getWebinarOrEventAttributeSet()
    {
        if ($this->webinarOrEventAttributeSet === null) {
            $collection = $this->attributeSetCollectionFactory->create();
            $collection->setEntityTypeFilter($this->productResource->getTypeId())
                ->addFieldToFilter('attribute_set_name', EventOrWebinar::ATTRIBUTE_SET_NAME);

            $this->webinarOrEventAttributeSet = $collection->getFirstItem();
        }


        return $this->webinarOrEventAttributeSet;
    }
}