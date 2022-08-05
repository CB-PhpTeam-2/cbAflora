<?php

namespace Webkul\MpHyperLocal\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Webkul\MpHyperLocal\Model\ResourceModel\Outlet\CollectionFactory;

class Outlet implements ArgumentInterface
{
    /**
     * @param CollectionFactory $outletCollection
     */
    public function __construct(
        CollectionFactory $outletCollection
    ) {
        $this->outletCollection = $outletCollection;
    }

    /**
     * get Outlet Name
     *
     * @param string $items
     * @return string
     */
    public function getOutletName($outlet = '')
    {
        $outletCollection = $this->outletCollection->create()
                                 ->addFieldToFilter('source_code', $outlet)
                                 ->getFirstItem();
        return $outletCollection->getOutletName();
    }
}
