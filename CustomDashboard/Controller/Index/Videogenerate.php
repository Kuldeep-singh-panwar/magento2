<?php
namespace Testing\CustomDashboard\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Testing\CustomDashboard\Model\VideoRecordFactory;

class Videogenerate extends Action
{
    protected $resultPageFactory;
    protected $resultJsonFactory;
    protected $logger;
    protected $videoRecordFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,              // LoggerInterface inject करें
        VideoRecordFactory $videoRecordFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;              // Assign logger
        $this->videoRecordFactory = $videoRecordFactory;  // Assign videoRecordFactory
    }

    public function execute()
    {
        /** AJAX REQUEST */
        if ($this->getRequest()->isXmlHttpRequest()) {

            // ObjectManager हटा दिया - already injected dependencies use करें
            $result = $this->resultJsonFactory->create();

            try {
                $postData = $this->getRequest()->getPostValue();

                $this->logger->info("Video API Called - Starting request");                
                
                $curl = curl_init();
                $headers = [
                    'authorization: 7fA9cD3QxL2M8RkP6JwH4ZVbN5eYtSUm',
                    'xapikey: sk_0cdfe4c0c3c82f902d7524cb226f31ac04d919552afe5f219a8440c96fc8d3a8',                    
                ];

                $postData['image'] = 'https://aiappbuilder.co.in/assets/images/products/9dee849f6951_3_813.jpg';
                
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://5575-182-70-244-233.ngrok-free.app/api/generate-ai-ads-video",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_POSTFIELDS => $postData,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]);

                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                
                if (curl_errno($curl)) {
                    $error = curl_error($curl);
                    curl_close($curl);
                    
                    return $result->setData([
                        'success' => false,
                        'message' => 'cURL Error: ' . $error
                    ]);
                }

                curl_close($curl);
                
                $data = json_decode($response, true);   

                // डेटाबेस में सेव करें
                if (isset($data['status']) && $data['status'] == 'success') {
                    
                    /** @var \Testing\CustomDashboard\Model\VideoRecord $videoRecord */
                    $videoRecord = $this->videoRecordFactory->create();
                    
                    $videoRecord->setData([
                        'video_url'     => $data['video_url'] ?? '',
                        'status'        => $data['status'],
                        'response_data' => json_encode($data),
                        'product_id'    => $postData['product_id'] ?? 0,
                    ]);
                    
                    $videoRecord->save();

                    $this->logger->info('Video record saved with ID: ' . $videoRecord->getId());
                }
                
                return $result->setData([
                    'success' => ($data['status'] == 'success') ? true : false,
                    'data'    => $data,  // यहाँ 'data' key में रैप किया
                    'http_code' => $httpCode
                ]);

            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());  // $logger की जगह $this->logger
                
                return $result->setData([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        /** PAGE VIEW */
        $productId = $this->getRequest()->getParam('id');
        $videoType = $this->getRequest()->getParam('type', '');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set('Generate Video');

        $block = $resultPage->getLayout()->getBlock('video.generation');

        if ($block) {
            $block->setData('product_id', $productId);
            $block->setData('video_type', $videoType);
        }

        return $resultPage;
    }
}