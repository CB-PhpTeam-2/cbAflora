<?php

namespace Cb\GreenlineApi\Controller\Index;
use \Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class Index extends \Magento\Framework\App\Action\Action {

    const GREENLINEAPI_IMPORT_SELLER_HISTORY_TABLE = 'greenlineapi_seller_history';

    protected $logger;
    protected $_objectManager;
    protected $_moduleHelper;
    protected $_orderExportHelper;
    protected $_orderStatusHelper;
    protected $_productImportHelper;
    protected $_invoiceHelper;
    protected $_shipmentHelper;
    protected $_createPosCustomerHelper;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Cb\GreenlineApi\Helper\OrderExport $orderExportHelper,
        \Cb\GreenlineApi\Helper\OrderStatus $orderStatusHelper,
        \Cb\GreenlineApi\Helper\ProductImport $productImportHelper,
        \Cb\GreenlineApi\Helper\CreateInvoice $invoiceHelper,
        \Cb\GreenlineApi\Helper\CreateShipment $shipmentHelper,
        \Cb\GreenlineApi\Helper\CreatePosCustomer $createPosCustomerHelper
    ){
        $this->logger = $loggerInterface;
        $this->_objectManager = $objectManager;
        $this->_moduleHelper = $moduleHelper;
        $this->_orderExportHelper = $orderExportHelper;
        $this->_orderStatusHelper = $orderStatusHelper;
        $this->_productImportHelper = $productImportHelper;
        $this->_invoiceHelper = $invoiceHelper;
        $this->_shipmentHelper = $shipmentHelper;
        $this->_createPosCustomerHelper = $createPosCustomerHelper;
        parent::__construct($context);
    }

    public function execute(){

        if($this->_moduleHelper->chkIsModuleEnable()){

            //$this->_createPosCustomerHelper->getPosCustomerName();

            //$this->logger->debug('GreenlineApi Cron --- Start');
            //$orderId = $this->getRequest()->getParam('orderId');
            //$sellerId = 1;
            //$this->_invoiceHelper->generateInvoice($orderId, $sellerId);
            //$this->_shipmentHelper->generateShipment($orderId, $sellerId);
            //die('hhhh');

            /*  --------  export Orders -----  start  ---- */
            $orderId = $this->getRequest()->getParam('orderId');
            //$this->_orderExportHelper->exportOrder($orderId);
            /*  --------  export Orders -----   end  ---- */

            /*  --------  update Order Status -----  start  ---- */
            //$this->_orderStatusHelper->updateOrderStatus();
            /*  --------  update Order Status -----   end  ---- */

            /*  --------  import Products -----  start  ---- */
            $serviceName = "Products";
            $this->_moduleHelper->insertGreenlineSellerHistory();
            $sellerId = $this->_moduleHelper->getNeedToUpdateSeller();

            if($sellerId){
                $response = $this->_moduleHelper->getCurlResponse($serviceName, $sellerId);
                $this->_productImportHelper->importProducts($response,$serviceName,$sellerId);
            }
            /*  --------  import Products -----   end  ---- */

            //$this->logger->debug('GreenlineApi Cron --- End');
        }
        
    }  

}
