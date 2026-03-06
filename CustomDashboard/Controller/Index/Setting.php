<?php
namespace Testing\CustomDashboard\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Setting extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        
        // Force layout to load
        $resultPage->addHandle('testing_customdashboard_index_setting');
        $resultPage->addHandle('dashboard_index_setting');
        
        $resultPage->getConfig()->getTitle()->set(__('User Registration'));
        
        return $resultPage;
    }
}