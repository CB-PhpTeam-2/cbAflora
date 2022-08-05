<?php

namespace Cb\GreenlineApi\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;

class DeleteCustomerRestrict
{
	protected $_messageManager;
	
	public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_messageManager = $messageManager;
    }
	
    public function aroundDeleteById(
        \Magento\Customer\Model\ResourceModel\CustomerRepository $subject,
        \Closure $proceed,
        $customerId
    ) {
        // You can add your logic here
        if ($customerId == 48) {
            $this->_messageManager->addError("This is default seller.!!");
            throw new NoSuchEntityException(
                __('This Seller can not be delete.!!')
            );
        }

        // It will proceed ahead with the default delete function        
        $result = $proceed($customerId);

        return $result;
    }
}