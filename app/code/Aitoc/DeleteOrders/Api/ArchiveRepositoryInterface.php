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

use Aitoc\DeleteOrders\Api\Data\ArchiveInterface;

interface ArchiveRepositoryInterface
{
    /**
     * @param \Aitoc\DeleteOrders\Api\Data\ArchiveInterface $archiveModel
     *
     * @return \Aitoc\DeleteOrders\Api\Data\ArchiveInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(ArchiveInterface $archiveModel);

    /**
     * @param int $entityId
     *
     * @return \Aitoc\DeleteOrders\Api\Data\ArchiveInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($entityId);

    /**
     * @param \Aitoc\DeleteOrders\Api\Data\ArchiveInterface $archiveModel
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(ArchiveInterface $archiveModel);

    /**
     * @param int $entityId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);
}
