<?php
namespace Cb\GreenlineApi\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderSuccessObserver implements ObserverInterface
{
    protected $_resource;
    protected $_moduleHelper;
    protected $_orderExportHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Cb\GreenlineApi\Helper\OrderExport $orderExportHelper
    )
    {
        $this->_resource = $resource;
        $this->_moduleHelper = $moduleHelper;
        $this->_orderExportHelper = $orderExportHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->_moduleHelper->chkIsModuleEnable()){
            $order = $observer->getEvent()->getOrder();
            if($order){
                $connection  = $this->_resource->getConnection();
                $OrderId = $order->getId();

                //if($order->getCustomerId() > 0){
                    $this->_orderExportHelper->exportOrder($OrderId);  
                //}
            }
        }
        return $this;
    }
}
