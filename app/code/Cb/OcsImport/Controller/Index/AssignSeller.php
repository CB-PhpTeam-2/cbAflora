<?php

namespace Cb\OcsImport\Controller\Index;
use \Magento\Framework\App\Action\Context;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;

class AssignSeller extends \Magento\Framework\App\Action\Action {

    protected $logger;
    protected $_objectManager;
    protected $product;
	protected $_greenlineHelper;
    protected $_moduleHelper;
    protected $mpProductFactory;
	protected $_date;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product $product,
        \Cb\GreenlineApi\Helper\Data $greenlineHelper,
		\Cb\OcsImport\Helper\Data $moduleHelper,
		MpProductFactory $mpProductFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date
    ){
        $this->logger = $loggerInterface;
        $this->_objectManager = $objectManager;
        $this->product = $product;
        $this->_greenlineHelper = $greenlineHelper;
		$this->_moduleHelper = $moduleHelper;
		$this->mpProductFactory = $mpProductFactory;
		$this->_date = $date;
        parent::__construct($context);
    }

    public function execute(){

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ocsproduct-assign-seller.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

		$logger->info("OCS product assign seller cron --- Start.");

		/*  --------  assign default seller -----  start  ---- */
		
		$defaultSellerId = $this->_greenlineHelper->getDefaultSellerId();

		if($defaultSellerId){
			$serviceName = "AssignSeller";
			$response = $this->_moduleHelper->getCollection($serviceName);
			
			foreach($response as $data){
				if(trim($data['seller_id']) == ''){
					$productId = $data['entity_id'];
					$product = $this->product->load($productId);
					$status = $product->getStatus();
					
					$mpProductModel = $this->mpProductFactory->create();
					$mpProductModel->setMageproductId($productId);
					$mpProductModel->setSellerId($defaultSellerId);
					$mpProductModel->setStatus($status);
					$mpProductModel->setAdminassign(1);
					$mpProductModel->setIsApproved(1);
					$mpProductModel->setCreatedAt($this->_date->gmtDate());
					$mpProductModel->setUpdatedAt($this->_date->gmtDate());
					$mpProductModel->save();
				}
			}
			$this->_moduleHelper->setOffset($serviceName);
		}
		/*  --------  assign default seller -----  end  ---- */


		$logger->info("OCS product assign seller cron --- End.");   
    }  

}
