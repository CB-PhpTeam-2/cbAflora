<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Api;

use Aitoc\DeleteOrders\Api\Data\RulesInterface;

interface RulesRepositoryInterface
{
    /**
     * @param \Aitoc\DeleteOrders\Api\Data\RulesInterface $rulesModel
     *
     * @return \Aitoc\DeleteOrders\Api\Data\RulesInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(RulesInterface $rulesModel);

    /**
     * @param int $entityId
     *
     * @return \Aitoc\DeleteOrders\Api\Data\RulesInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($entityId);

    /**
     * @param \Aitoc\DeleteOrders\Api\Data\RulesInterface $rulesModel
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(RulesInterface $rulesModel);

    /**
     * @param int $entityId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);
}
