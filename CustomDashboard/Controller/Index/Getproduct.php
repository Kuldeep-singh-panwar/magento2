<?php
namespace Testing\CustomDashboard\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Testing\CustomDashboard\Block\Dashboard;

class Getproduct implements HttpGetActionInterface
{
    protected $request;
    protected $resultJsonFactory;
    protected $productRepository;
    protected $dashboardBlock;

    public function __construct(
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        Dashboard $dashboardBlock
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->dashboardBlock = $dashboardBlock;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $productId = $this->request->getParam('id');
        // echo"<pre>";print_r($productId);die;
        if (!$productId) {
            return $result->setData(['error' => 'Product ID is required']);
        }
        
        try {
            $product = $this->productRepository->getById($productId);
            $productData = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $this->dashboardBlock->getFormattedPrice($product->getFinalPrice()),
                'description' => $product->getShortDescription() ?: $product->getDescription(),
                'image' => $this->dashboardBlock->getProductImageUrl($product, 300, 300),
                'url' => $product->getProductUrl(),
                'cartUrl' => $this->dashboardBlock->getAddToCartUrl($product)
            ];
            return $result->setData($productData);
        } catch (NoSuchEntityException $e) {
            return $result->setData(['error' => 'Product not found']);
        } catch (\Exception $e) {
            return $result->setData(['error' => 'An error occurred']);
        }
    }
}