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

class ActionOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    const ACTION_ARCHIVE = 1;
    const ACTION_DELETE = 0;

    public static function getOptionArray()
    {
        return [
            self::ACTION_ARCHIVE => __('Archive'),
            self::ACTION_DELETE => __('Delete')
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
