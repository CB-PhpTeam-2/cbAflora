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
use Aitoc\DeleteOrders\Model\RulesFactory;
use Aitoc\DeleteOrders\Model\RulesRepository;

/**
 * Class Save
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\Rules
 */
class Save extends Action
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
     * @var RulesFactory
     */
    protected $rulesFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param RulesRepository $rulesRepository
     * @param RulesFactory $rulesFactory
     */
    public function __construct(
        Context $context,
        RulesRepository $rulesRepository,
        RulesFactory $rulesFactory
    ) {
        $this->rulesRepository = $rulesRepository;
        $this->rulesFactory = $rulesFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost() && $postData = $this->getRequest()->getPostValue()) {
            try {
                if ($ruleId = $this->getRequest()->getParam("entity_id")) {
                    $ruleModel = $this->rulesRepository->get($ruleId);
                } else {
                    $ruleModel = $this->rulesFactory->create();
                }
                $data = [
                    'title' => $postData["title"],
                    'scope' => $postData["scope"],
                    'action' => $postData["action"],
                    'time' => $postData["time"],
                    'is_active' => $postData["is_active"],
                    'order_statuses' => implode(",", $postData["order_statuses"])
                ];
                $ruleModel->addData($data);
                $this->rulesRepository->save($ruleModel);
                $this->messageManager->addSuccessMessage(__('You saved the rule'));
                $redirectResult->setPath('deleteorders/rules');
                return $redirectResult;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__("Invalid request"));
        }
        $redirectResult->setPath('deleteorders/rules');
        return $redirectResult;

    }
}
