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

namespace Webkul\SellerSubAccount\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

class SubAccountPermissions extends \Magento\Backend\Block\Template
{

    public $_template = 'Webkul_SellerSubAccount::seller/sub_account_permission.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\SellerSubAccount\Helper\Data $subAccountHelper,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEditBlock,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->subAccountHelper = $subAccountHelper;
        $this->customerEditBlock = $customerEditBlock;
        $this->_sellerModel = $sellerModel;
        parent::__construct($context, $data);
    }
    /**
     * GetSellerModel function
     *
     * @return Array
     */
    public function getSellerModel()
    {
        return $this->_sellerModel->create()
        ->load($this->getCustomerId())
        ->getSubAccountPermission();
    }
    /**
     * GetsellerAdminPermission function get list of seller to sub seller permission by admin
     *
     * @return array
     */
    public function getsellerAdminPermission()
    {
        return $this->subAccountHelper->getSellerPermissionForSubSellerByAdmin();
    }
    /**
     * GetAllPermissionsObject function
     *
     * @return void
     */
    public function getAllPermissionsObject()
    {
        return  $this->subAccountHelper->getAllPermissionTypes();
    }

    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    public function canShowTab()
    {
        $coll = $this->customerEditBlock->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return true;
        }
        return false;
    }

    public function getTabLabel()
    {
        return __('Sub Account Permissions');
    }

    public function getTabTitle()
    {
        return __('Sub Account Permissions');
    }

    public function getTabClass()
    {
        return '';
    }

    public function getTabUrl()
    {
        return '';
    }

    public function isAjaxLoaded()
    {
        return false;
    }

    public function isHidden()
    {
        return false;
    }
}
