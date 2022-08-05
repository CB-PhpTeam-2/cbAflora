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

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\SessionFactory;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;

class SaveArea extends AbstractAccount
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    protected $shipAreaFactory;
    
    /**
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param SessionFactory $customerSessionFactory
     * @param ShipAreaFactory $shipAreaFactory
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        SessionFactory $customerSessionFactory,
        ShipAreaFactory $shipAreaFactory
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->customerSessionFactory  = $customerSessionFactory;
        $this->shipAreaFactory  = $shipAreaFactory;
        parent::__construct($context);
    }

    /**
     * Ship area save
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath(
                'mphyperlocal/account/addshiparea',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $wholedata = $this->getRequest()->getParams();
        list($errors, $wholedata) = $this->validatePost($wholedata);
        if (!empty($errors)) {
            foreach ($errors as $message) {
                $this->messageManager->addError($message);
            }
        } else {
            $sellerId = $this->customerSessionFactory->create()->getCustomerId();
            $shipAreaCollection = $this->shipAreaFactory->create()->getCollection()
                                    ->addFieldToFilter('latitude', $wholedata['latitude'])
                                    ->addFieldToFilter('longitude', $wholedata['longitude'])
                                    ->addFieldToFilter('seller_id', $sellerId);
            if (!$shipAreaCollection->getSize()) {
                $shipArea = $this->shipAreaFactory->create();
                $wholedata['seller_id'] = $sellerId;
                $shipArea->setData($wholedata);
                $shipArea->save();
                $this->messageManager->addSuccess(__('Ship area saved successfully.'));
            } else {
                $this->messageManager->addError(__('Ship area already exist.'));
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mphyperlocal/account/addshiparea'));
    }

    /**
     * Validate Form Data
     *
     * @param array $formData
     * @return array
     */
    public function validatePost($formData)
    {
        $errors = [];
        $data = [];
        foreach ($formData as $field => $value) {
            switch ($field) {
                case 'address':
                    $result = $this->validateAddressTypeField($value, $field, $data);
                    if ($result['error']) {
                        $errors[] = __('Address has to be completed');
                        $formData[$field] = '';
                    } else {
                        $formData[$field] = $result['data'][$field];
                    }
                    break;
                case 'address_type':
                    $result = $this->validateAddressTypeField($value, $field, $data);
                    if ($result['error']) {
                        $errors[] = __('Address Type has to be completed');
                        $formData[$field] = '';
                    } else {
                        $formData[$field] = $result['data'][$field];
                    }
                    break;
                case 'latitude':
                    $result = $this->validateCoOrdinateTypeField($value, $field, $data);
                    if ($result['error']) {
                        $errors[] = __('Latitude should contain only decimal numbers');
                        $formData[$field] = '';
                    } else {
                        $formData[$field] = $result['data'][$field];
                    }
                    break;
                case 'longitude':
                    $result = $this->validateCoOrdinateTypeField($value, $field, $data);
                    if ($result['error']) {
                        $errors[] = __('Longitude should contain only decimal numbers');
                        $formData[$field] = '';
                    } else {
                        $formData[$field] = $result['data'][$field];
                    }
                    break;
            }
        }

        return [$errors, $formData];
    }

    /**
     * Validate Address Type Fields for Form Data
     *
     * @param string $value
     * @param string $field
     * @param array $data
     * @return array
     */
    public function validateAddressTypeField($value, $field, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$field] = strip_tags($value);
        }
        return ['error' => $error, 'data' => $data];
    }

    /**
     * Validate Address Type Fields for Form Data
     *
     * @param float $value
     * @param string $field
     * @param array $data
     * @return array
     */
    public function validateCoOrdinateTypeField($value, $field, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$field] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }
}
