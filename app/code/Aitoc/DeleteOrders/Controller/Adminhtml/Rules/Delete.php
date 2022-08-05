<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Aitoc\DeleteOrders\Model\RulesRepository;

/**
 * Class Delete
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\Rules
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aitoc_DeleteOrders::rules';

    /**
     * @var RulesRepository
     */
    protected $rulesRepository;

    /**
     * Restore constructor.
     * @param Context $context
     * @param RulesRepository $rulesRepository
     */
    public function __construct(Context $context, RulesRepository $rulesRepository)
    {
        $this->rulesRepository = $rulesRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        if ($ruleId = $this->getRequest()->getParam("entity_id")) {
            try {
                $rule = $this->rulesRepository->get($ruleId);
                $this->rulesRepository->delete($rule);
                $this->messageManager->addSuccessMessage(__('Rule was successfully deleted'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $redirectResult->setPath('deleteorders/rules');
        return $redirectResult;

    }
}
