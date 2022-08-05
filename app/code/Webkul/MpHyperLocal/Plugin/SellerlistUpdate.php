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
namespace Webkul\MpHyperLocal\Plugin;

class SellerlistUpdate
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    private $hyperLocalHelper;

    public function __construct(
        \Webkul\MpHyperLocal\Helper\Data $hyperLocalHelper
    ) {
        $this->hyperLocalHelper = $hyperLocalHelper;
    }

    public function afterGetSellerCollection(
        \Webkul\Marketplace\Block\Sellerlist $list,
        $result
    ) {
        $localSellerIds = $this->hyperLocalHelper->getNearestSellers();
        $result->addFieldToFilter('seller_id', ['in' => $localSellerIds]);
        return $result;
    }
}
