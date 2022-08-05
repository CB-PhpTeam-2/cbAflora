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

use Magento\Framework\Data\OptionSourceInterface;

class ShipAreaOption implements OptionSourceInterface
{
    /**
     * Get all options.
     *
     * @return array
     */
    public function toOptionArray()
    {

        $options = [
            ['value' => 'city', 'label' => __('City')],
            ['value' => 'state', 'label' => __('State')],
            ['value' => 'country', 'label' => __('Country')]
        ];

        return $options;
    }
}
