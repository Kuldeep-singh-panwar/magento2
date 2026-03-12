<?php
namespace Testing\CustomDashboard\Model\ResourceModel\VideoRecord;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Testing\CustomDashboard\Model\VideoRecord::class,
            \Testing\CustomDashboard\Model\ResourceModel\VideoRecord::class
        );
    }
}