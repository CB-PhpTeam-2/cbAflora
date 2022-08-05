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
namespace Webkul\MpTwilioSMSNotification\Helper;
require __DIR__ . '/../../../../../vendor/twilio/sdk/src/Twilio/autoload.php';
use Webkul\MpTwilioSMSNotification\Validator\PhoneNumberValidator;
use Magento\Framework\Message\ManagerInterface as MessageManager;

/**
 * Helper for fetching module configuration parameters,
 * performing common tasks, and loggin errors.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const TWILLO_ACCOUNT_SID = 'twilio/twiliosettings/accountsid';
    const TWILLO_AUTH_TOKEN = 'twilio/twiliosettings/authtoken';
    const TWILIO_SENDER_NUMBER = 'twilio/twiliosettings/twiliophonenumber';
    const CUSTOMER_NOTIFICATION_ENABLED = 'twilio/twiliosettings/customer_notification';
    const TWILIO_MODULE_STATUS = 'twilio/twiliosettings/enabled';
    const TWILIO_ACCOUNT_MODE = 'twilio/twiliosettings/account';

    const MARKETPLACE_ORDER_HISTORY_CONTROLLER = 'marketplace/order/history';
    const SALES_ORDER_HISTORY_CONTROLLER = 'sales/order/history';

    /**
     * @var Magento\Customer\Model\Customer
     */
    private $customerModel;

    /**
     * @var \Webkul\MpTwilioSMSNotification\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Webkul\MpTwilioSMSNotification\Logger\Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\Url
     */
    private $urlModel;

    /**
     * @var PhoneNumberValidator
     */
    private $phoneNumberValidator;

    /**
     * @param \Magento\Customer\Model\Customer                 $customerModel
     * @param \Magento\Framework\App\Helper\Context            $context
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Webkul\MpTwilioSMSNotification\Logger\Logger    $logger
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager
     * @param \Magento\Framework\Url                           $urlModel
     */
    public function __construct(
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Webkul\MpTwilioSMSNotification\Logger\Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $urlModel,
        PhoneNumberValidator $phoneNumberValidator
    ) {
        parent::__construct($context);
        $this->customerModel = $customerModel;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->urlModel = $urlModel;
        $this->phoneNumberValidator = $phoneNumberValidator;
    }

    /**
     * Get whether Twilio is enabled in the admin panel
     *
     * @return bool
     */
    public function getTwilioStatus()
    {
        return $this->scopeConfig->getValue(
            self::TWILIO_MODULE_STATUS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get whether sending SMS notification to Customer
     * is enabled or not in admin panal
     *
     * @return bool
     */
    public function isCustomerNotificationEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CUSTOMER_NOTIFICATION_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Account mode of Twilio
     *
     * @return bool
     */
    public function getMode()
    {
        return $this->scopeConfig->getValue(
            self::TWILIO_ACCOUNT_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Twilio phone number which is used to send messages
     *
     * @return string
     */
    public function getTwilioPhoneNumber()
    {
        return $this->scopeConfig->getValue(
            self::TWILIO_SENDER_NUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get current current store base url
     *
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get Customer Model by seller ID
     *
     * @param  int $sellerId contain seller id
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer($sellerId)
    {
        return $this->customerModel->load($sellerId);
    }

    /**
     * Create and return a new instance of Twilio client
     *
     * @return \Twilio\Rest\Client|null return null if failed to create
     *         Twlilo client for some reason
     */
    public function makeTwilloClient()
    {
        try {
            $accountSID = $this->scopeConfig->getValue(
                self::TWILLO_ACCOUNT_SID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $authToken = $this->scopeConfig->getValue(
                self::TWILLO_AUTH_TOKEN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $twilioClient = new \Twilio\Rest\Client(
                $this->encryptor->decrypt($accountSID),
                $this->encryptor->decrypt($authToken)
            );

            return $twilioClient;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data sendMessage : " . $e->getMessage());

            return null;
        }
    }
    
    /**
     * Send SMS notification message to receivers phone number using Twlio
     *
     * @param  \Twilio\Rest\Client $twilioClient
     * @param  string              $reciver
     * @param  string              $message
     * @return void
     */
    public function sendMessage(
        $twilioClient,
        $reciverPhoneNumber,
        $message = null
    ) {
        try {
            if (substr($reciverPhoneNumber, 0, 2) != '+1') {
                $reciverPhoneNumber = '+1'.$reciverPhoneNumber;
            }
			
			/*if (substr($reciverPhoneNumber, 0, 3) != '+91') {
                $reciverPhoneNumber = '+91'.$reciverPhoneNumber;
            }*/
			
            $twilioClient->messages->create(
                $reciverPhoneNumber,
                [
                    'from' => $this->getTwilioPhoneNumber(),
                    'body' => $message
                ]
            );
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data sendMessage : " . $e->getMessage());
        }
    }

    /**
     * Verify phone number with Twilio
     *
     * @param  \Twilio\Rest\Client $twilioClient
     * @param  string              $phoneNumberToVerify
     * @return array
     */
    public function verifyPhoneNumber($twilioClient, $phoneNumberToVerify)
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(true);
        if (!($twilioClient instanceof \Twilio\Rest\Client)) {
            return $response->setMessage(__("Credentials are required to verify phone number"));
        }
        if ($this->phoneNumberValidator->isEmptyNoTrim($phoneNumberToVerify)) {
            return $response->setMessage(__("Phone number is required"));
        }
        if (!$this->phoneNumberValidator->isFormatValid($phoneNumberToVerify)) {
            return $response->setMessage(
                __("Please enter a valid phone number with country code (Ex: +918888888888)")
            );
        }
        try {
            $account = $twilioClient->getAccount()->fetch();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data verifyNumber : " . $e->getMessage());
            return $response->setMessage(__("Invalid Twilio account credentials"));
        }

        try {
            $validation_request = $twilioClient->validationRequests
            ->create(
                $phoneNumberToVerify,
                [
                    "friendlyName" => "Api verified Numbers"
                ]
            );
            $response->setError(false);
            return $response->setMessage(__("Validation code") . ": " . $validation_request->validationCode);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = substr($message, (strpos($message, ":") ?: -2) + 2);
            $this->logDataInLogger("Helper_Data verifyNumber : " . $message);
            return $response->setMessage(__($message));
        }
    }

    /**
     * Log data in logger
     * @param  string $data
     * @return void
     */
    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }

    /**
     * Get Order History Url for Customer
     *
     * @return string
     */
    public function getSalesOrderHistoryUrl()
    {
        return $this->urlModel->getUrl(
            self::SALES_ORDER_HISTORY_CONTROLLER,
            ['_nosid' => true]
        );
    }

    /**
     * Get Order History url for Marketplace Seller
     *
     * @return string
     */
    public function getMpOrderHistoryUrl()
    {
        return $this->urlModel->getUrl(
            self::MARKETPLACE_ORDER_HISTORY_CONTROLLER,
            ['_nosid' => true]
        );
    }

    /**
     * Get order item simple name(with product options like color, size, etc appended) if available
     *
     * @param  \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return string
     */
    public function getOrderItemName($orderItem)
    {
        $name = $orderItem->getName();
        $options = $orderItem->getProductOptions();
        $name = empty($options['simple_name']) ? $name : $options['simple_name'];

        return $name;
    }

    /**
     * Get customer telephone number from order
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return string|null
     */
    public function getCustomerTelephone($order)
    {
        $telephone = empty($order->getShippingAddress())
            ? null
            : $order->getShippingAddress()->getTelephone();
        $telephone = empty($telephone)
            ? (empty($order->getBillingAddress()) ? null : $order->getBillingAddress()->getTelephone())
            : $telephone;
        $telephone = str_replace(" ", "", $telephone);

        return $telephone;
    }

    /**
     * Get customer name from order
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getCustomerName($order)
    {
        return $order->getCustomerFirstname() ?: $order->getCustomerName();
    }

    /**
     * Get seller order items from list of order items
     *
     * @param  \Magento\Sales\Api\Data\OrderItemInterface[] $orderItems
     * @param  string                                       $sellerOrderItemProductIds
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getSellerOrderItems($orderItems, $sellerOrderItemProductIds)
    {
        $sellerOrderItems = [];
        foreach ($orderItems as $orderItem) {
            $orderItemProductId = $orderItem->getProductId();
            $productIdPattern = "/^\s*$orderItemProductId\s*$|" .
                        "^\s*$orderItemProductId\s*,|" .
                        ",\s*$orderItemProductId\s*$|" .
                        ",\s*$orderItemProductId\s*,/";
            if (preg_match($productIdPattern, $sellerOrderItemProductIds)) {
                $sellerOrderItems[] = $orderItem;
            }
        }

        return $sellerOrderItems;
    }

    /**
     *
     * @param  \Magento\Sales\Api\Data\OrderItemInterface[] $orderItems
     * @param  string                                       $sellerOrderItemProductIds
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getOrderItemsExcludingSeller($orderItems, $sellerOrderItemProductIds)
    {
        $orderItemsExcludingSeller = [];
        foreach ($orderItems as $orderItem) {
            $orderItemProductId = $orderItem->getProductId();
            $productIdPattern = "/^\s*$orderItemProductId\s*$|" .
                        "^\s*$orderItemProductId\s*,|" .
                        ",\s*$orderItemProductId\s*$|" .
                        ",\s*$orderItemProductId\s*,/";
            if (!preg_match($productIdPattern, $sellerOrderItemProductIds)) {
                $orderItemsExcludingSeller[] = $orderItem;
            }
        }

        return $orderItemsExcludingSeller;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $orderItemsStringRepresentation
     * @param MessageManager $messageManager
     * @return bool
     */
    public function sendSMSToCustomer(
        \Magento\Sales\Model\Order $order,
        string $orderItemsStringRepresentation,
        MessageManager $messageManager,
        string $messageContent = ""
    ): bool {
        if (empty($messageContent)) {
            $messageContent = "Hi %1, the Order %2 for item(s) %3 has been cancelled, Please visit %4 or" .
                " check your mail %5 for more details";
        }
        $twilioClient = $this->makeTwilloClient();
        $orderIncrementId = $order->getIncrementId();
        $customerName = $this->getCustomerName($order);
        $customerTelephone = $this->getCustomerTelephone($order);
        $customerOrderHistoryUrl = $this->getSalesOrderHistoryUrl();
        $customerEmail = $order->getCustomerEmail();
        $content = __(
            $messageContent,
            $customerName,
            $orderIncrementId,
            $orderItemsStringRepresentation,
            $customerOrderHistoryUrl,
            $customerEmail
        );
        try {
            $this->sendMessage(
                $twilioClient,
                $customerTelephone,
                $content
            );
        } catch (\Throwable $e) {
            $this->logDataInLogger($e->getMesssage());
            $messageManager->addError($e->getMessage());

            return false;
        }

        return true;
    }
}
