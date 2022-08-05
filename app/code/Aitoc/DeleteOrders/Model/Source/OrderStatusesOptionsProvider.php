<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Model\Source;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class OrderStatusesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var $statusCollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * @param \Magento\Store\Model\System\Store $store
     */
    public function __construct(CollectionFactory $statusCollectionFactory)
    {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * Get status options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();
        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getStatusOptions();
    }
}
