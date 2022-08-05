<?php
namespace Cb\RecommendationTool\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper {

    const Was_PostCode_XML_PATH_EXTENSIONS = 'postcode/general/';

	protected $scopeConfig;
	protected $_objectManager;
    protected $_storeManager;
    protected $_resource;
    protected $logger;
	
	public function __construct(
		\Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
	)
    {
        $this->scopeConfig = $context->getScopeConfig();
		$this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
    }

    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function chkIsModuleEnable(){

    	return $this->getModuleConfig(self::Was_PostCode_XML_PATH_EXTENSIONS . 'isenabled');
    }

}
