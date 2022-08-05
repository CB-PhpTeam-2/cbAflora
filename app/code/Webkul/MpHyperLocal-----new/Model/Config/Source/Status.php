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

namespace Webkul\MpHyperLocal\Model\Config\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                "value"=>1,
                "label"=>__("Enabled")
            ],
            [
                "value"=>0,
                "label"=>__("Disabled")
            ]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [1=>__("Enabled"), 2=>__("Disabled")];
    }
}
