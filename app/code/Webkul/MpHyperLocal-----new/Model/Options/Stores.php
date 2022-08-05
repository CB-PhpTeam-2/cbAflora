<?php
namespace Webkul\MpHyperLocal\Model\Options;

class Stores implements \Magento\Framework\Option\ArrayInterface
{
    
    /**
     * construct function
     *
     * @param \Webkul\Marketplace\Model\SellerFactory $collectionFactory
     */
    public function __construct(
        \Webkul\Marketplace\Model\SellerFactory $collectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * returns seller option.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        $customerCollection = $this->_collectionFactory->create()->getCollection()->addFieldToSelect('seller_id');
        $status = $this->_collectionFactory->create()::STATUS_ENABLED;
        // $sellerbadge_table = $customerCollection->getTable('mpsellerbadge');
        // $badgeTable = $customerCollection->getTable('mpbadges');
        $customerTable = $customerCollection->getTable('customer_grid_flat');
        $customerCollection->getSelect()
        ->join(
            $customerTable.' as cgf',
            'main_table.seller_id = cgf.entity_id',
            [
                'name'=>'name',
                'email'=>'email',
            ]
        )
        // ->joinLeft(
        //     $sellerbadge_table. ' as mpsb',
        //     'main_table.seller_id = mpsb.seller_id',
        //     []
        // )
        // ->joinLeft(
        //     $badgeTable. ' as mpb',
        //     'mpsb.badge_id = mpb.entity_id',
        //     [
        //         'badge' => 'GROUP_CONCAT(mpb.badge_name , " ")'
        //     ]
        // )
         ->where('main_table.store_id = 0 AND main_table.is_seller='.$status)->group("main_table.seller_id");
        if ($customerCollection->getSize()) {
            foreach ($customerCollection as $badgeData) {
                // $badge = $badgeData->getBadge();
                // if ($badge == '') {
                //     $badge = __('No badges yet!!');
                // }
                $arr[] = [
                    'value' => $badgeData->getSellerId(),
                    // 'label' => $badgeData->getName().' ('.$badge.')'
                    'label' => $badgeData->getName()
                ];
            }
        }
        return $arr;
    }
}
