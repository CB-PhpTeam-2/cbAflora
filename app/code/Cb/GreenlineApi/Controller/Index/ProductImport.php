<?php

namespace Cb\GreenlineApi\Controller\Index;
use \Magento\Framework\App\Action\Context;

class ProductImport extends \Magento\Framework\App\Action\Action {

    protected $logger;
    protected $_objectManager;
    protected $_moduleHelper;
    protected $_productImportHelper;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Cb\GreenlineApi\Helper\ProductImport $productImportHelper
    ){
        $this->logger = $loggerInterface;
        $this->_objectManager = $objectManager;
        $this->_moduleHelper = $moduleHelper;
        $this->_productImportHelper = $productImportHelper;
        parent::__construct($context);
    }

    public function execute(){

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/greenline-product-import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        if($this->_moduleHelper->chkIsModuleEnable()){

            $logger->info("GreenlineApi product import cron --- Start.");

            /*  --------  import Products -----  start  ---- */
            $serviceName = "Products";
            $this->_moduleHelper->insertGreenlineSellerHistory();
            $sellerId = $this->_moduleHelper->getNeedToUpdateSeller();

            if($sellerId){
                $response = $this->_moduleHelper->getCurlResponse($serviceName, $sellerId);
                $this->_productImportHelper->importProducts($response,$serviceName,$sellerId);
            }
            /*  --------  import Products -----   end  ---- */

            $logger->info("GreenlineApi product import cron --- End.");
        }
        
    }  

}
