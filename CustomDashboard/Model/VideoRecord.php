<?php
namespace Testing\CustomDashboard\Model;

use Magento\Framework\Model\AbstractModel;

class VideoRecord extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Testing\CustomDashboard\Model\ResourceModel\VideoRecord::class);
    }
}