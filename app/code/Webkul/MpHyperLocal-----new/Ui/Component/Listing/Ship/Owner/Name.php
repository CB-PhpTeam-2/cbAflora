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
namespace Webkul\MpHyperLocal\Ui\Component\Listing\Ship\Owner;

/**
 * Class Status.
 */
class Name extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $columnIndex = $this->getData('name');
                $item[$columnIndex] = $item[$columnIndex]? $item[$columnIndex] : __('Admin');
            }
        }
        return $dataSource;
    }
}
