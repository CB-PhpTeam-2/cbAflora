<?php

namespace Cb\GreenlineApi\Ui\Component\Listing\Column;

class PaymentStatus extends \Magento\Ui\Component\Listing\Columns\Column {

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ){
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        $paymentStatus = 'unpaid';
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if($item['is_paid'] == 1){
                    $paymentStatus = 'paid';
                }
                $item['is_paid'] = $paymentStatus;
            }
        }

        return $dataSource;
    }
}