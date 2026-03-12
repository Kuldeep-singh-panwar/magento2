<?php
namespace Testing\CustomDashboard\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class GeneratePrompt extends Action
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resultJsonFactory = $objectManager->create(\Magento\Framework\Controller\Result\JsonFactory::class);
        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
        
        $result = $resultJsonFactory->create();

        try {
            $postData = $this->getRequest()->getPostValue();

            // echo "<pre>";print_r($postData);exit;
            $logger->info("Video API Called - Starting request");

            $imageUrl = $this->getRequest()->getParam('image', 'https://aiappbuilder.co.in/assets/images/products/9dee849f6951_3_813.jpg');
            $type = $postData['type'];
            
            $curl = curl_init();
            $headers = [
                'authorization: 7fA9cD3QxL2M8RkP6JwH4ZVbN5eYtSUm',
                'xapikey: sk_0cdfe4c0c3c82f902d7524cb226f31ac04d919552afe5f219a8440c96fc8d3a8',
                'source: magento',
                'domain;',
                'storeid;'
            ];

            $postData['image'] = 'https://aiappbuilder.co.in/assets/images/products/9dee849f6951_3_813.jpg';

            $postFields = [
                'image' => $imageUrl,
                'type' => $type,
                '' => ''
            ];

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://5575-182-70-244-233.ngrok-free.app/api/generate-video-prompt",
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
            return $result->setData([
                'success' => true,
                'data' => $data,
                'http_code' => $httpCode
            ]);

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
            
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}