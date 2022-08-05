<?php

namespace Cb\GreenlineApi\Cron;

class ImportProducts
{   
    protected $logger;
    protected $_objectManager;
    protected $_resource;
    protected $_moduleHelper;
    protected $_productImportHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Cb\GreenlineApi\Helper\ProductImport $productImportHelper
    )
    {
        $this->logger = $loggerInterface;
        $this->_objectManager = $objectManager;
        $this->_resource = $resource;
        $this->_moduleHelper = $moduleHelper;
        $this->_productImportHelper = $productImportHelper;
    }

    public function execute() {

        if($this->_moduleHelper->chkIsModuleEnable()){
            
            $this->logger->debug('GreenlineApi Products Import Cron --- Start');
            /*  --------  import Products -----  start  ---- */
            $serviceName = "Products";
            $this->_moduleHelper->insertGreenlineSellerHistory();
            $sellerId = $this->_moduleHelper->getNeedToUpdateSeller();

            if($sellerId){
                $response = $this->_moduleHelper->getCurlResponse($serviceName, $sellerId);
                $this->_productImportHelper->importProducts($response,$serviceName,$sellerId);
            }
            /*  --------  import Products -----   end  ---- */
            $this->logger->debug('GreenlineApi Products Import Cron --- End');
        }
    }

}
