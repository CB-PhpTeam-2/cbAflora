<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mangopay
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @var int
     */
    public $loggerType = SellerSubAccountLogger::CRITICAL;

    /**
     * File name.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @var string
     */
    public $fileName = '/var/log/SellerSubAccount.log';
}
