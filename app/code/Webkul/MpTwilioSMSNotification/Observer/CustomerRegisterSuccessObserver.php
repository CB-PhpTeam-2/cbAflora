<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTwilioSMSNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpTwilioSMSNotification\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpTwilioSMSNotification\Helper\Data
     */
    protected $_helperData;
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param Data                                        $helperData
     * @param MpHelper                                    $helperMarketplace
     * @param ManagerInterface                            $messageManager
     */
    public function __construct(
        Data $helperData,
        ManagerInterface $messageManager,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_messageManager = $messageManager;
        $this->_helperData = $helperData;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helperData->getTwilioStatus()) {
            $customer = $observer->getCustomer();
            $customerId = $customer->getId();
            //$customer = $this->customerRepository->getById($customerId);
            $client = $this->_helperData->makeTwilloClient();
            
            if($customerId){
                $customerName = $customer->getFirstName().' '.$customer->getLastName();
                $toNumber = str_replace(
                    " ",
                    "",
                    $customer->getCustomAttribute('mobile_number')->getValue()
                );
                $body = __(
                    "Hi %1, thank you for registering on %2",
                    $customerName,
                    $this->_helperData->getSiteUrl()
                );

                try {
                    $this->_helperData->sendMessage($client, $toNumber, $body);
                } catch (\Exception $e) {
                    $this->_messageManager->addError($e->getMessage());
                }
            }
        }
    }
}
