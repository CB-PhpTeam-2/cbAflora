<?php

namespace Cb\GreenlineApi\Controller\Index;
use \Magento\Framework\App\Action\Context;

class OrderStatus extends \Magento\Framework\App\Action\Action {


    protected $logger;
    protected $_objectManager;
    protected $_moduleHelper;
    protected $_orderStatusHelper;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Cb\GreenlineApi\Helper\OrderStatus $orderStatusHelper
    ){
        $this->logger = $loggerInterface;
        $this->_objectManager = $objectManager;
        $this->_moduleHelper = $moduleHelper;
        $this->_orderStatusHelper = $orderStatusHelper;
        parent::__construct($context);
    }

    public function execute(){

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/greenline-order-status.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        if($this->_moduleHelper->chkIsModuleEnable()){

            $logger->info("GreenlineApi Order Status cron --- Start.");

            /*  --------  update Order Status -----  start  ---- */
            $this->_orderStatusHelper->updateOrderStatus();
            /*  --------  update Order Status -----   end  ---- */

            $logger->info("GreenlineApi Order Status cron --- End.");
        }
        
    }  

}
