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

class CheckAssignProduct
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

    public function afterGetAssignProductCollection(
        \Webkul\MpAssignProduct\Helper\Data $subject,
        $result
    ) {
        $isEnabled = $this->hyperLocalHelper->isEnabled();
        if ($isEnabled) {
            $sellerIds = $this->hyperLocalHelper->getNearestSellers();
            $result->addFieldToFilter("main_table.seller_id", ["in" => $sellerIds]);
        }
        return $result;
    }
}
