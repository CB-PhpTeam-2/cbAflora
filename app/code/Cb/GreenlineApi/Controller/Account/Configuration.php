<?php

namespace Cb\GreenlineApi\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Cb\GreenlineApi\Model\GreenlineCredentialFactory;
use Webkul\Marketplace\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Configuration extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

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

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SellerFactory $sellerFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SellerFactory $sellerFactory,
        GreenlineCredentialFactory $greenlineCredentialFactory,
        Data $data,
        EncryptorInterface $encryptor,
        FormKeyValidator $formKeyValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->sellerFactory = $sellerFactory;
        $this->greenlineCredentialFactory = $greenlineCredentialFactory;
        $this->_mpHelper = $data;
        $this->encryptor = $encryptor;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }
    
    /**
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
                        'greenlineapi/account/configuration',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $data = $this->getRequest()->getParams();

                $sellerData = $this->sellerFactory->create()->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                if ($sellerData->getSize()) {
                        //$encryptApiKey = $this->encryptor->encrypt($data['api_key']);
                        $config = $this->greenlineCredentialFactory->create();
                        $configData = $this->greenlineCredentialFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('seller_id', $sellerId);
                        $allowupdate = 1;
                        if ($configData->getSize() > 0) {
                            $id = $configData->getFirstItem()->getId();
                            //$existApiKey = $configData->getFirstItem()->getApiKey();
                            $config->setId($id);

                            /*if($this->encryptor->decrypt($data['api_key']) == $this->encryptor->decrypt($existApiKey)){
                                $allowupdate = 0;
                            }*/
                        }
                        $config->setStatus($data['status']);
                        $config->setSellerId($sellerId);
                        $config->setCompanyId($data['company_id']);
                        $config->setLocationId($data['location_id']);

                        /*if($allowupdate == 1){
                            $config->setApiKey($encryptApiKey);
                        }*/
                        $config->save();
                    
                    $this->messageManager->addSuccess(__('Your Greenline Credential has been successfully saved.'));
                } else {
                    $this->messageManager->addError(__('Invalid seller'));
                }

                return $this->resultRedirectFactory->create()->setPath('greenlineapi/account/configuration');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('greenlineapi/account/configuration');
            }
        } elseif ($this->_mpHelper->isSeller()) {
            $resultPage = $this->resultPageFactory->create();
            if ($this->_mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('greenlineapi_layout2_account_configuration');
            }

            $resultPage->getConfig()->getTitle()->set(__('Add Greenline Credential'));
            return $resultPage;
        } else {
            $this->_forward('defaultNoRoute');
        }
    }
}
