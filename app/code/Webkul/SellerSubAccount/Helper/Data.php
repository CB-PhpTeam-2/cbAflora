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
namespace Webkul\SellerSubAccount\Helper;

use Magento\Framework\App\Helper\Context;
use Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface;
use Webkul\SellerSubAccount\Model\SubAccount;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer\Mapper as CustomerMapper;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magento\Customer\Model\Session;

/**
 * Webkul SellerSubAccount Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var SubAccountRepositoryInterface
     */
    public $_subAccountRepository;

    /**
     * @var SubAccount
     */
    public $subAccount;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    public $_customerRepository;

    /**
     * @var CustomerMapper
     */
    public $_customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    public $_customerFactory;

    /**
     * @var AccountManagementInterface
     */
    public $_accountManagement;

    /**
     * @var DataObjectHelper
     */
    public $_dataObjectHelper;

    /**
     * @var GroupCollection
     */
    public $_groupCollection;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $helperData;

    /**
     * @var ControllersRepository
     */
    public $_controllersRepository;

    /**
     * @var ResultPageFactory
     */
    public $resultPageFactory;

    /**
     * @param Context $context
     * @param SubAccountRepositoryInterface                 $subAccountRepository
     * @param SubAccount                                    $subAccount
     * @param \Magento\Customer\Model\Session               $customerSession
     * @param CustomerRepositoryInterface                   $customerRepository
     * @param CustomerMapper                                $customerMapper
     * @param CustomerInterfaceFactory                      $customerFactory
     * @param AccountManagementInterface                    $accountManagement,
     * @param DataObjectHelper                              $dataObjectHelper
     * @param GroupCollection                               $groupCollection
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Webkul\Marketplace\Helper\Data               $helperData
     * @param ControllersRepository                         $controllersRepository
     * @param ResultPageFactory                             $resultPageFactory
     */
    public function __construct(
        Context $context,
        SubAccountRepositoryInterface $subAccountRepository,
        SubAccount $subAccount,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CustomerMapper $customerMapper,
        CustomerInterfaceFactory $customerFactory,
        AccountManagementInterface $accountManagement,
        DataObjectHelper $dataObjectHelper,
        GroupCollection $groupCollection,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Webkul\Marketplace\Helper\Data $helperData,
        ControllersRepository $controllersRepository,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel,
        \Webkul\SellerSubAccount\Model\SubAccountFactory $subAccountSeller,
        ResultPageFactory $resultPageFactory
    ) {
        $this->_subAccountRepository = $subAccountRepository;
        $this->subAccount = $subAccount;
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerFactory = $customerFactory;
        $this->_accountManagement = $accountManagement;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_groupCollection = $groupCollection;
        $this->_moduleList = $moduleList;
        $this->helperData = $helperData;
        $this->_controllersRepository = $controllersRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->_sellerModel = $sellerModel;
        $this->subAccountSeller = $subAccountSeller;
        parent::__construct($context);
    }
    public function getAccountGroup()
    {
        $groupId = 1;
        $coll = $this->_groupCollection
            ->addFieldToFilter('customer_group_code', 'Sub Account');
        foreach ($coll as $key => $value) {
            $groupId = $value->getId();
        }
        return $groupId;
    }

    public function getAccountGeneralGroup()
    {
        $groupId = 0;
        $coll = $this->_groupCollection
            ->addFieldToFilter('customer_group_code', 'General');
        foreach ($coll as $key => $value) {
            $groupId = $value->getId();
        }
        if (!$groupId) {
            $groupId = 1;
            $coll = $this->_groupCollection;
            foreach ($coll as $key => $value) {
                $groupId = $value->getId();
            }
        }
        return $groupId;
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    public function getCustomerId()
    {
        return $this->getCustomerSession()->getCustomerId();
    }

    /**
     * Get Sub Account By Id.
     * @param int $id
     *
     * @return collection object
     */
    public function getSubAccountById($id)
    {
        $subAccount = $this->_subAccountRepository->get($id);
        return $subAccount;
    }

    /**
     * getCurrentSubAccount.
     *
     * @return Webkul\SellerSubAccount\Model\SubAccount
     */
    public function getCurrentSubAccount()
    {
        $customerId = $this->getCustomerId();
        return $this->_subAccountRepository->getActiveByCustomerId($customerId);
    }

    /**
     * getSubAccountSellerId.
     *
     * @return int
     */
    public function getSubAccountSellerId()
    {
        return $this->getCurrentSubAccount()->getSellerId();
    }

    /**
     * Get Sub Account By Id.
     *
     * @return bool
     */
    public function isSubAccount()
    {
        $customerId = $this->getCustomerId();
        $subAccount = $this->_subAccountRepository->getByCustomerId($customerId);
        if ($subAccount->getId()) {
            return 1;
        }
        return 0;
    }

    /**
     * isActive.
     *
     * @return bool
     */
    public function isActive()
    {
        $customerId = $this->getCustomerId();
        $subAccount = $this->_subAccountRepository->getByCustomerId($customerId);
        if ($subAccount->getStatus()) {
            return 1;
        }
        return 0;
    }

    /**
     * Get Customer By Id.
     * @param int $customerId
     *
     * @return collection object
     */
    public function getCustomerById($customerId)
    {
        $customer = $this->_customerRepository->getById($customerId);
        return $customer;
    }

    /**
     * Prepare Marketplace Mapped Labels.
     *
     * @return array
     */
    public function getMappedLabelsArr()
    {
        return [
          'marketplace/order/history' => 'Manage Orders',
          'marketplace/order/view' => 'Manage Orders',
          'marketplace/product/productlist' => 'View Products',
          'marketplace/product/add' => 'Manage Products',
          'marketplace/account/dashboard' => 'View Dashboard',
          'marketplace/account/editprofile' => 'Manage Profile',
          'marketplace/product_attribute/new' => 'Create Configurable Product Attribute',
          'marketplace/transaction/history' => 'Manage Transaction',
          'marketplace/order/shipping' => 'Manage Order pdf header information'
        ];
    }
    /**
     * getAllPermissionTypes.
     *
     * @return array
     */
    public function getAllPermissionTypes()
    {
        $options = [];
        $labelArr = $this->getMappedLabelsArr();
        $modules = $this->_moduleList->getNames();
        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $modules = $dispatchResult->toArray();
        sort($modules);
    
        foreach ($modules as $moduleName) {
            if (strpos($moduleName, 'Webkul') !== false && ($moduleName !== 'Webkul_SellerSubAccount')) {
                $controllersList = $this->_controllersRepository->getByModuleName($moduleName);
                foreach ($controllersList as $key => $value) {
                    $path = $value['controller_path'];
                    $label = $value['label'];
                    if (array_key_exists($path, $labelArr)) {
                        $label = $labelArr[$path];
                    }
                    $options[$path] = __($label);
                }
            }
        }
        foreach ($modules as $moduleName) {
            if (strpos($moduleName, 'Webkul_MpRmaSystem') !== false && ($moduleName !== 'Webkul_SellerSubAccount')) {
                $options["mprmasystem/seller/allrma"] = __("Marketplace RMA");
            }
        }
        if (!$this->helperData->getIsSeparatePanel()) {
            unset($options['marketplace/account/customer']);
            unset($options['marketplace/account/review']);
        }
        return $options;
    }

    /**
     * function getAllAllowedActions to get all allowed subaccount actions.
     *
     * @return array
     */
    public function getAllAllowedActions()
    {
        $allAllowedActions = [];
        $subAccount = $this->getCurrentSubAccount();
        if ($subAccount->getId()) {
            $allowedPermissionType = explode(',', $subAccount->getPermissionType());
            $adminPermission = $this->getSellerPermissionByCustomerId();
            $commonPermission = array_intersect($allowedPermissionType, $adminPermission);
            $mappedControllers = $this->getAllPermissionTypes();
            $mappedControllers = array_change_key_case($mappedControllers, CASE_LOWER);
            foreach ($commonPermission as $path) {
                $path = strtolower($path);
                if (!empty($mappedControllers[$path])) {
                    $allAllowedActions[$path] = $mappedControllers[$path];
                }
            }
        }
        // if (!empty($allAllowedActions['marketplace/product/add'])) {
        //     $allAllowedActions['marketplace/product/productlist'] = __('View Products');
        // }
        return $allAllowedActions;
    }

    public function manageSubAccounts()
    {
        return $this->scopeConfig->getValue(
            'sellersubaccount/general_settings/manage_sub_accounts',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Save Customer Data.
     *
     * @param array $postData
     * @param int $id
     */
    public function saveCustomerData($customerData, $customerId = 0, $websiteId = 0)
    {
        if (!empty($customerData)) {
            try {
                
                // optional fields might be set in request for future processing
                // by observers in other modules
                $customerData['website_id'] = $websiteId;
                $customerData['group_id'] = $this->getAccountGroup();
                $customerData['disable_auto_group_change'] = 0;
                $customerData['prefix'] = '';
                $customerData['middlename'] = '';
                $customerData['suffix'] = '';
                $customerData['taxvat'] = '';
                // $customerData['dob'] = '';
                // $customerData['gender'] = '';
                $customerData['default_billing'] = '';
                $customerData['default_shipping'] = '';
                $customerData['confirmation'] = '';
                $customerData['sendemail_store_id'] = 1;

                if ($this->dobMandatory() && array_key_exists('dob', $customerData)) {
                    $birthday = $customerData['dob'];
                    $timestamp = strtotime($birthday);
                    $customerDob = date("Y-m-d", $timestamp);
                    $customerData['dob'] = $customerDob;
                } else {

                    $customerDob = date("Y-m-d");
                    $customerData['dob'] = $customerDob;

                }

                if (!array_key_exists("gender", $customerData)) {
                    $customerData['gender'] = 'other';
                }

                if ($customerId) {
                    $currentCustomer = $this->_customerRepository->getById($customerId);
                    $customerData = array_merge(
                        $this->_customerMapper->toFlatArray($currentCustomer),
                        $customerData
                    );
                    $customerData['id'] = $customerId;
                }
                /** @var CustomerInterface $customer */
                $customer = $this->_customerFactory->create();
                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                // Save customer
                if ($customerId) {
                    $this->_customerRepository->save($customer);

                    $this->getEmailNotification()->credentialsChanged(
                        $customer,
                        $currentCustomer->getEmail()
                    );
                } else {
                    $customer = $this->_accountManagement->createAccount($customer);
                    $customerId = $customer->getId();

                    $this->getEmailNotification()->credentialsChanged(
                        $customer,
                        $customer->getEmail()
                    );
                }
            } catch (\Exception $e) {
                return ['error'=>1, 'message'=>$e->getMessage()];
            }
        }
        return ['error'=>0, 'customer_id'=>$customerId];
    }

    public function dobMandatory()
    {
        $dob = $this->scopeConfig->getValue(
            'customer/address/dob_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($dob == "req") {
            return true;
        } else {
            return false;
        }
    }

    public function genderMandatory()
    {
        $gender = $this->scopeConfig->getValue(
            'customer/address/gender_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($gender == "req") {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Save Customer Group Data.
     *
     * @param array $postData
     * @param int $id
     */
    public function saveCustomerGroupData($customerId = 0, $websiteId = 0)
    {
        if ($customerId) {
            try {
                // optional fields might be set in request for future processing
                // by observers in other modules
                $customerData['group_id'] = $this->getAccountGeneralGroup();
                $currentCustomer = $this->_customerRepository->getById($customerId);
                $customerData = array_merge(
                    $this->_customerMapper->toFlatArray($currentCustomer),
                    $customerData
                );
                $customerData['id'] = $customerId;
                /** @var CustomerInterface $customer */
                $customer = $this->_customerFactory->create();
                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                // Save customer
                $this->_customerRepository->save($customer);
            } catch (\Exception $e) {
                return ['error'=>1, 'message'=>$e->getMessage()];
            }
        }
        return ['error'=>0, 'customer_id'=>$customerId];
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated
     */
    public function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Get child blocks
     *
     * @param string
     * @return array
     */
    public function getAllChildBlocksByBlockName($blockName)
    {
        $resultPage = $this->resultPageFactory->create();
        $blockInstance = $resultPage->getLayout()->getBlock($blockName);
        return $blockInstance->getChildNames();
    }

    /**
     * Is child blocks allowed
     *
     * @return boolean
     */
    public function isAllowedChildMenu()
    {
        $shippingMenu = $this->getAllChildBlocksByBlockName(
            'layout2_seller_account_navigation_shipping_menu'
        );
        $paymentMenu = $this->getAllChildBlocksByBlockName(
            'layout2_seller_account_navigation_payment_menu'
        );
        $flag = false;
        if (count($shippingMenu)) {
            $actionNames = $this->getAllShippingActions();
            foreach ($actionNames as $actionName) {
                if ($this->helperData->isAllowedAction($actionName)) {
                    return true;
                }
            }
        }
        if (count($paymentMenu)) {
            $actionNames = $this->getAllPaymentActions();
            foreach ($actionNames as $actionName) {
                if ($this->helperData->isAllowedAction($actionName)) {
                    return true;
                }
            }
        }
        return $flag;
    }

    /**
     * @return array
     */
    public function getAllShippingActions()
    {
        return [
            'mpups/shipping/view',
            'mpshipping/shippingset/view',
            'mpshipping/shipping/view',
            'mpdhl/shipping/view',
            'endicia/account/config',
            'endicia/account/manage',
            'baseshipping/shipping',
            'mpfrenet/shipping/index',
            'auspost/shipping/view',
            'mpfastway/shipping/view',
            'canadapost/shipping/view/',
            'canadapost/shipping/view',
            'mpdelhivery/orders/index',
            'mparamex/shipping/view',
            'multiship/shipping/view',
            'mpfedex/shipping/view',
            'freeshipping/shipping/view',
            'mpfixrate/shipping/view',
            'mpusps/shipping/view',
            'easypost/shipping/view',
            'timedelivery/account/index'
        ];
    }

    /**
     * @return array
     */
    public function getAllPaymentActions()
    {
        return [
            'mpbraintree/braintreeaccount/index',
            'mpmoip/seller/connect',
            'iyzico/onboard/merchant',
            'mpmasspay/paypal/index',
            'mpcitruspayment/sellerdetail/index',
            'mpcitruspayment/sellerdetail',
            'mercadopago/seller/permission',
            'mpmangopay/bankdetail',
            'mpmangopay/sellerkyc',
            'mpmangopay/seller/transaction',
            'mpstripe/seller/connect'
        ];
    }
    public function getControllerMappedPermissionsSellerSubAccount()
    {
        return [
            'marketplace/account/askquestion' => 'marketplace/account/dashboard',
            'marketplace/account_dashboard/tunnel' => 'marketplace/account/dashboard',
            'marketplace/account/chart' => 'marketplace/account/dashboard',
            'marketplace/account/becomesellerPost' => 'marketplace/account/becomeseller',
            'marketplace/account/deleteSellerBanner' => 'marketplace/account/editProfile',
            'marketplace/account/deleteSellerLogo' => 'marketplace/account/editProfile',
            'marketplace/account/editProfilePost' => 'marketplace/account/editProfile',
            'marketplace/account/rewriteUrlPost' => 'marketplace/account/editProfile',
            'marketplace/account/savePaymentInfo' => 'marketplace/account/editProfile',
            'mprmasystem/seller/rma' => 'mprmasystem/seller/rma'
        ];
    }
    public function getSellerPermissionForSubSellerByAdmin()
    {
        
        $individualSellerPermission = $this->_sellerModel->create()
        ->load($this->getCustomerId())
        ->getSubAccountPermission();
        $permissions = explode(',', $individualSellerPermission);
        if ($individualSellerPermission != null) {
            return explode(',', $individualSellerPermission);
        } else {
            $list = $this->scopeConfig->getValue(
                'sellersubaccount/sub_account_permission/manage_sub_account_permission',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return explode(',', $list);
        }
    }

    public function getSellerPermissionByCustomerId()
    {
        $sellerId = $this->getSubAccountSellerId();
        $individualSellerPermission = $this->_sellerModel->create()
        ->load($sellerId)
        ->getSubAccountPermission();
        if ($individualSellerPermission != null) {
            return explode(',', $individualSellerPermission);
        } else {
            $list = $this->scopeConfig->getValue(
                'sellersubaccount/sub_account_permission/manage_sub_account_permission',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return explode(',', $list);
        }
    }

    /**
     * isAllowForBecomeSeller
     *
     * @return boolean
     */
    public function isAllowForBecomeSeller()
    {
        $flag = true;
        if ($this->isSubAccount() && !$this->isActive()) {
            $flag = false;
        }
        return $flag;
    }
}
