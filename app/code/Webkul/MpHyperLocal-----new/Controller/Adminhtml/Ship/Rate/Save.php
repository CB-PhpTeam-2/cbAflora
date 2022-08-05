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
namespace Webkul\MpHyperLocal\Controller\Adminhtml\Ship\Rate;

use Magento\Framework\Locale\Resolver;
use Webkul\MpHyperLocal\Model\ShipRateFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ShipRateFactory $shipRate
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->shipRate = $shipRate;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $data = $this->getRequest()->getPostValue();
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
                $count = 0;
                while (!feof($fileHandle)) {
                    $row = fgetcsv($fileHandle, 1024);
                    if ($this->isValidRow($row)) {
                        $sellerId = isset($row[5])? $row[5]:null;
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
                $this->messageManager->addError(__('Please upload CSV file'));
            }
            return $this->_redirect('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('*/*/new');
        }
    }

    /**
     * _isAllowed
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpHyperLocal::shiparea_add');
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
