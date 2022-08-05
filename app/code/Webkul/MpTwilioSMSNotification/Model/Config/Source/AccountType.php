<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTwilioSMSNotification\Model\Config\Source;

class AccountType
{
    const DISABLE = 0;
    const ENABLE = 1;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_manager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Module\Manager $manager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $manager
    ) {
        $this->_manager = $manager;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data = [
                    [
                        'value'=>self::DISABLE,
                        'label'=>__('Sandbox')
                    ],
                    [
                        'value'=>self::ENABLE,
                        'label'=>__('Live')
                    ]
                ];
        return $data;
    }
}
