<?php

namespace Paysafe\Payment\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Webkul\Marketplace\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Configuration extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    private $_customerFactory;

    /**
     * @var Webkul\Marketplace\Model\SellerFactory
     */
    private $sellerFactory;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    protected $_resource;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ShipRateFactory $shipRate
     * @param SellerFactory $sellerFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SellerFactory $sellerFactory,
        CollectionFactory $customerFactory,
        Data $data,
        EncryptorInterface $encryptor,
        \Magento\Framework\App\ResourceConnection $resource,
        FormKeyValidator $formKeyValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->sellerFactory = $sellerFactory;
        $this->_customerFactory = $customerFactory;
        $this->_mpHelper = $data;
        $this->encryptor = $encryptor;
        $this->_resource = $resource;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }
    
    /**
     * Add Shipping Origin page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $sellerId = $this->_mpHelper->getCustomerId();
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'paysafe/account/configuration',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $data = $this->getRequest()->getParams();
                $connection  = $this->_resource->getConnection();
                $sellerData = $this->sellerFactory->create()->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                if ($sellerData->getSize()) {
                    $encryptApiSecretKey = $this->encryptor->encrypt($data['paysafe_api_secret_key']);
                    $configData = $this->_customerFactory->create()
                                    ->addFieldToFilter('entity_id', $sellerId);
                    if ($configData->getSize() > 0) {
                        $id = $configData->getFirstItem()->getEntityId();
                        $existApiSecretKey = $configData->getFirstItem()->getPaysafeApiSecretKey();

                        if($existApiSecretKey != '' && $this->encryptor->decrypt($data['paysafe_api_secret_key']) == $this->encryptor->decrypt($existApiSecretKey)){
                                unset($data['paysafe_api_secret_key']);
                        }else{
                            $data['paysafe_api_secret_key'] = $encryptApiSecretKey;
                        }

                        $updatedData = $data;
                        unset($updatedData['form_key']);
                        $where = ['entity_id = ?' => (int)$id];
                 
                        $tableName = $connection->getTableName("customer_entity");
                        $connection->update($tableName, $updatedData, $where);

                        $this->messageManager->addSuccess(__('Your Paysafe Credential has been successfully saved.'));
                    }

                } else {
                    $this->messageManager->addError(__('Invalid seller'));
                }

                return $this->resultRedirectFactory->create()->setPath('paysafe/account/configuration');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('paysafe/account/configuration');
            }
        } elseif ($this->_mpHelper->isSeller()) {
            $resultPage = $this->resultPageFactory->create();
            if ($this->_mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('paysafe_layout2_account_configuration');
            }

            $resultPage->getConfig()->getTitle()->set(__('Add Paysafe Credential'));
            return $resultPage;
        } else {
            $this->_forward('defaultNoRoute');
        }
    }
}
