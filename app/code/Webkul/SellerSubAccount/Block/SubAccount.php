<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Block;

class SubAccount extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Webkul\Marketplace\Helper\Data $selAccHelper

     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\SellerSubAccount\Helper\Data $subAccHelper,
        array $data = []
    ) {
        $this->_mpHelper = $mpHelper;
        $this->_subAccHelper = $subAccHelper;
        parent::__construct($context, $data);
    }
}
