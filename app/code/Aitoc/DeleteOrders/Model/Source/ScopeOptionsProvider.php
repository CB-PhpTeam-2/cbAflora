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

class ScopeOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    const SALES_GRID_SCOPE = 1;
    const ARCHIVE_GRID_SCOPE = 0;

    public static function getOptionArray()
    {
        return [
            self::SALES_GRID_SCOPE => __('Sales Order Grid'),
            self::ARCHIVE_GRID_SCOPE => __('Archive Order Grid')
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
