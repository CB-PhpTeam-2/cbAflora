<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class RuleStatus
 */
class RuleStatus implements OptionSourceInterface
{
    const RULE_STATUS_ENABLE = 1;
    const RULE_STATUS_DISABLE = 0;

    public static function getOptionArray()
    {
        return [
            self::RULE_STATUS_ENABLE => __('Enabled'),
            self::RULE_STATUS_DISABLE => __('Disabled')
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (self::getOptionArray() as $index => $value) {
            $options[] = ['value' => $index, 'label' => $value];
        }
        return $options;
    }
}
