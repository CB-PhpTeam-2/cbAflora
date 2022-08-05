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

class MarketplaceUpdate
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

    public function afterGetBestSaleSellers(
        \Webkul\Marketplace\Block\Marketplace $list,
        $result
    ) {
        $localSellerIds = $this->hyperLocalHelper->getNearestSellers();
        foreach ($localSellerIds as $value) {
            foreach ($result as $key => $valueResult) {
                foreach ($valueResult as $sellerId => $data) {
                    if (!in_array($sellerId, $localSellerIds)) {
                        unset($result[$key][$sellerId]);
                    }
                }
            }
        }
        return $result;
    }
}
