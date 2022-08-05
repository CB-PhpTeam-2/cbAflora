<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\MpHyperLocal\Model\ShipRateFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Helper\Data;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class AddRate extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Webkul\MpHyperLocal\Model\ShipRateFactory
     */
    private $shipRate;

    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

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
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        ShipRateFactory $shipRate,
        SellerFactory $sellerFactory,
        Data $data,
        FormKeyValidator $formKeyValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->shipRate = $shipRate;
        $this->customerSession = $customerSession;
        $this->sellerFactory = $sellerFactory;
        $this->_mpHelper = $data;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Add Shipping rate page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        if ($this->getRequest()->isPost()) {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath(
                    'mphyperlocal/account/addrate',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            try {
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'rate-csv-file']
                );
                $result = $uploader->validateFile();
                $file = $result['tmp_name'];
                $fileNameArray = explode('.', $result['name']);
                $ext = end($fileNameArray);
                if ($file != '' && $ext == 'csv') {
                    $fileHandle = fopen($file, 'r');
                    $sellerId = $this->customerSession->getCustomerId();
                    $count = 0;
                    while (!feof($fileHandle)) {
                        $row = fgetcsv($fileHandle, 1024);
                        if ($this->isValidRow($row)) {
                            $temp = [
                                'distance_from' => $row[0],
                                'distance_to' => $row[1],
                                'weight_from' => $row[2],
                                'weight_to' => $row[3],
                                'cost' => $row[4],
                                'seller_id' => $sellerId,
                            ];
                            $this->saveShipRate($temp);
                            $count++;
                        }
                    }
                    $this->messageManager->addSuccess(__('Your shipping rate has been successfully updated'));
                } else {
                    $this->messageManager->addError(__('Please upload Csv file'));
                }
                return $this->resultRedirectFactory->create()->setPath('mphyperlocal/account/addrate');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('mphyperlocal/account/addrate');
            }
        } else {
            $sellerId = $this->customerSession->getCustomerId();
            $sellerData = $this->sellerFactory->create()->getCollection()
                                            ->addFieldToFilter('seller_id', $sellerId)
                                            ->setPageSize(1)->getFirstItem();
            if ($sellerData->getIsSeller()) {
                $resultPage = $this->resultPageFactory->create();
                if ($this->_mpHelper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mphyperlocal_layout2_account_addrate');
                }
                $resultPage->getConfig()->getTitle()->set(__('Add Shipping Rate'));
                return $resultPage;
            } else {
                $this->_forward('defaultNoRoute');
            }
        }
    }

    /**
     * isValidRow
     * @param array
     * @return bool
     */
    private function isValidRow($row)
    {
        for ($i = 0; $i < 4; $i++) {
            if (!is_numeric($row[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * saveShipRate
     * @param array $temp
     * @return void
     */
    private function saveShipRate($temp)
    {
        $shipRateRecord = $this->shipRate->create()->getCollection()
                                            ->addFieldToFilter('seller_id', $temp['seller_id'])
                                            ->addFieldToFilter('distance_from', $temp['distance_from'])
                                            ->addFieldToFilter('distance_to', $temp['distance_to'])
                                            ->addFieldToFilter('weight_from', $temp['weight_from'])
                                            ->addFieldToFilter('weight_to', $temp['weight_to'])
                                            ->setPageSize(1)->getFirstItem();
        if ($shipRateRecord->getEntityId()) {
            $shipRateRecord->setCost($temp['cost']);
            $shipRateRecord->setId($shipRateRecord->getId());
            $shipRateRecord->save();
        } else {
            $shippingModel = $this->shipRate->create();
            $shippingModel->setData($temp);
            $shippingModel->save();
        }
    }
}
