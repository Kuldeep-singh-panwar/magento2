<?php
namespace Testing\CustomDashboard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VideoRecord extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('video_records', 'entity_id');
    }
}