<?php

namespace Cb\GreenlineApi\Controller\SellerCron;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Run extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    protected $resultJsonFactory;

    protected $_objectManager;

    protected $_resource;

    protected $_moduleHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Cb\GreenlineApi\Helper\Data $moduleHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->_objectManager = $objectManager;
        $this->_resource = $resource;
        $this->_moduleHelper = $moduleHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $sellerId = (int) $this->getRequest()->getParam('seller_id');
        
        $response = [];
        if($this->getRequest()->isAjax() && $sellerId != ''){
            $connection  = $this->_resource->getConnection();
            $tablePrefix = $this->_moduleHelper->getTablePrefix();
            $table = $connection->getTableName($tablePrefix.'greenlineapi_sellercron_allow');
            $sellerHistorytable = $connection->getTableName($tablePrefix.'greenlineapi_seller_history');

            $query = "SELECT * FROM `".$table."` WHERE `seller_id` = $sellerId LIMIT 1";
            $collection = [];
            $collection = $connection->fetchAll($query);

            if(sizeof($collection) < 1){
                $tableData = [];
                $tableData[] = [$sellerId, 1];
                $connection->insertArray($table ,['seller_id', 'allow'], $tableData);
            }else{
                $allow = 1;
                $update_at = date('Y-m-d H:i:s', time());
                $updateQuery= "UPDATE `".$table."` SET `allow` = '$allow', `updated_at` =  '$update_at' WHERE `seller_id` = ".$sellerId;
                $connection->query($updateQuery);
            }

            $query = "SELECT * FROM `".$sellerHistorytable."` WHERE `seller_id` = $sellerId LIMIT 1";
            $collection = [];
            $collection = $connection->fetchAll($query);
            if(sizeof($collection) < 1){
                $tableData = [];
                $tableData[] = [$sellerId, 0];
                $connection->insertArray($sellerHistorytable ,['seller_id', 'status'], $tableData);
            }else{
                $updateQuery= "UPDATE `".$sellerHistorytable."` SET `status` = '0' WHERE `seller_id` = ".$sellerId;
                $connection->query($updateQuery);
            }
            
            $response = $connection->fetchAll($query);
            $response = $response[0];
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData(['seller_response' => $response]);
    }
}
