<?php

namespace Cb\GreenlineApi\Cron;

class UpdateOrderStatus
{
	protected $logger;
	protected $_moduleHelper;
	protected $_orderStatusHelper;

	public function __construct(
		\Psr\Log\LoggerInterface $loggerInterface,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Cb\GreenlineApi\Helper\OrderStatus $orderStatusHelper
	) {
		$this->logger = $loggerInterface;
		$this->_moduleHelper = $moduleHelper;
		$this->_orderStatusHelper = $orderStatusHelper;
	}

	public function execute() {

		if($this->_moduleHelper->chkIsModuleEnable()){
			$this->logger->debug('Greenline Order Status update Cron -- Start');
            /*  --------  update Order Status -----  start  ---- */
             $this->_orderStatusHelper->updateOrderStatus();
            /*  --------  update Order Status -----  end  ---- */
            $this->logger->debug('Greenline Order Status update Cron -- End');
	    }
	}
}