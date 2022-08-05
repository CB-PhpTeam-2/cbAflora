<?php

namespace Cb\GreenlineApi\Controller\Index;
use \Magento\Framework\App\Action\Context;

class InventoryImport extends \Magento\Framework\App\Action\Action {

    protected $logger;
    protected $_objectManager;
    protected $_moduleHelper;
    protected $_inventoryImportHelper;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Cb\GreenlineApi\Helper\InventoryImport $inventoryImportHelper
    ){
        $this->logger = $loggerInterface;
        $this->_objectManager = $objectManager;
        $this->_moduleHelper = $moduleHelper;
        $this->_inventoryImportHelper = $inventoryImportHelper;
        parent::__construct($context);
    }

    public function execute(){

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/greenline-inventory-import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        if($this->_moduleHelper->chkIsModuleEnable()){

            $logger->info("GreenlineApi inventory import cron --- Start.");

            /*  --------  import Inventory -----  start  ---- */
            $serviceName = "Inventory";
            $this->_moduleHelper->insertGreenlineSellerHistory();
            $sellerId = $this->_moduleHelper->getNeedToUpdateSellerInventoty();

            if($sellerId){
                $response = $this->_moduleHelper->getInventoryCurlResponse($sellerId);
                $this->_inventoryImportHelper->importInventory($response,$sellerId);
            }
            /*  --------  import Inventory -----   end  ---- */

            $logger->info("GreenlineApi inventory import cron --- End.");
        }
        
    }  

}
