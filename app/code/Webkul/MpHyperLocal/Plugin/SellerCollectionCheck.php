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

class SellerCollectionCheck
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

    public function after_getProductCollection(
        \Webkul\Marketplace\Block\Collection $list,
        $result
    ) {
        $sellerDetail = $list->getProfileDetail();
        $isAvilable = $this->hyperLocalHelper->isSellerAvilableInSavedLocation($sellerDetail['seller_id']);
        if (!$isAvilable) {
            $result->addAttributeToFilter('entity_id', ['eq' => 0]);
        }
        return $result;
    }
}
