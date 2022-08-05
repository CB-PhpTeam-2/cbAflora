<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubAccount\Controller\Account;

use Magento\Customer\Model\EmailNotificationInterface;

/**
 * Webkul SellerSubAccount Account Save Controller.
 */
class Save extends \Webkul\SellerSubAccount\Controller\AbstractSubAccount
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * Seller Sub Account Account Save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'sellersubaccount/account/manage',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $postData = $this->getRequest()->getPostValue();
                if (empty($postData['permission_type'])) {
                    $postData['permission_type'] = [];
                }
                if (empty($postData['status'])) {
                    $postData['status'] = 0;
                }
                $sellerId = $this->_helper->getCustomerId();
                $id = isset($postData['id'])
                    ? $postData['id']
                    : null;
                if (!empty($id)) {
                    // Check If sub account does not exists
                    $subAccount = $this->_subAccountRepository->get($id);
                    if ($subAccount->getId()) {
                        if ($subAccount->getSellerId() == $sellerId) {
                            $this->checkAndSaveCustomerData($id, $postData, $subAccount);
                        } else {
                            $this->messageManager->addError(
                                __('You are not authorized to update this sub account.')
                            );
                        }
                    } else {
                        $this->messageManager->addError(
                            __('Sub Account does not exist.')
                        );
                    }
                } else {
                    $result = $this->_helper->saveCustomerData($postData);
                    if (!empty($result['error']) && $result['error'] == 1) {
                        $this->messageManager->addError(
                            $result['message']
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            'sellersubaccount/account/manage',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    } else {
                        $customerId = $result['customer_id'];
                    }
                    $value = $this->_subAccount;
                    $value->setSellerId($sellerId);
                    $value->setCustomerId($customerId);
                    $value->setPermissionType(implode(',', $postData['permission_type']));
                    $value->setStatus($postData['status']);
                    $value->setCreatedDate($this->_date->gmtDate());
                    $id = $value->save()->getId();
                    $this->messageManager->addSuccess(
                        __('Sub Account was successfully created.')
                    );
                }
                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/edit',
                    ['id'=>$id, '_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/manage',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function checkAndSaveCustomerData($id = null, $postData = null, $subAccount = null)
    {
        
        $result = $this->_helper->saveCustomerData(
            $postData,
            $subAccount->getCustomerId(),
            $this->_marketplaceHelper->getWebsiteId()
        );
        if (!empty($result['error']) && $result['error'] == 1) {
            $this->messageManager->addError(
                $result['message']
            );
            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            $customerId = $result['customer_id'];
        }
        $value = $this->_subAccount->load($id);
        $value->setPermissionType(
            implode(',', $postData['permission_type'])
        );
        $value->setStatus($postData['status']);
        $value->save();
        $this->messageManager->addSuccess(
            __('Sub Account was successfully saved.')
        );
    }
}
