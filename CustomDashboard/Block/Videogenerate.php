<?php
namespace Testing\CustomDashboard\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Videogenerate extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var \Magento\Catalog\Model\Product|null
     */
    protected $product = null;
    
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
    }

    /**
     * Get product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        if ($this->product === null) {
            $productId = $this->getData('product_id');
            if ($productId) {
                try {
                    $this->product = $this->productRepository->getById($productId);
                } catch (NoSuchEntityException $e) {
                    return null;
                }
            }
        }
        return $this->product;
    }

    /**
     * Get product ID
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * Get video type
     *
     * @return mixed
     */
    public function getVideoType()
    {
        return $this->getData('video_type');
    }

    /**
     * Get available video types
     *
     * @return array
     */
    public function getVideoTypes()
    {
        return [
            'ai_ads' => 'AI Ads Videos',
            'product_showcase' => 'Product Showcase Videos',
            'fashion' => 'Fashion Videos',
            'promotional' => 'Promotional Videos',
            'tutorial' => 'Product Tutorial Videos'
        ];
    }
    
    /**
     * Get product data for video generation
     *
     * @return array
     */
    public function getProductData()
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }
        
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'description' => $product->getDescription(),
            'short_description' => $product->getShortDescription(),
            'image' => $this->getProductImageUrl($product),
            'url' => $product->getProductUrl()
        ];
    }
    
    /**
     * Get product image URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getProductImageUrl($product)
    {
        try {
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) 
                . 'catalog/product' . $product->getImage();
        } catch (\Exception $e) {
            return '';
        }
    }
    
    /**
     * Get video generation URL for AJAX
     *
     * @return string
     */
    public function getVideoGenerationUrl()
    {
        return $this->getUrl('customdashboard/index/videogenerate', ['_current' => true]);
    }
    
    /**
     * Get JSON encoded configuration
     *
     * @return string
     */
    public function getJsConfig()
    {
        $config = [
            'videoGenerationUrl' => $this->getVideoGenerationUrl(),
            'productData' => $this->getProductData(),
            'currentVideoType' => $this->getVideoType(),
            'videoTypes' => $this->getVideoTypes()
        ];
        
        return $this->jsonSerializer->serialize($config);
    }
    
    /**
     * Check if product exists
     *
     * @return bool
     */
    public function hasProduct()
    {
        return $this->getProduct() !== null;
    }
    
    /**
     * Get additional video parameters
     *
     * @return array
     */
    public function getVideoParameters()
    {
        $params = [];
        $product = $this->getProduct();
        
        if ($product) {
            $params['duration'] = 30; // Default duration in seconds
            $params['resolution'] = '1080p';
            $params['format'] = 'mp4';
            $params['aspect_ratio'] = '16:9';
            $params['background_music'] = true;
            $params['voice_over'] = true;
            $params['subtitles'] = false;
            
            // Add product-specific parameters
            $params['product_name'] = $product->getName();
            $params['product_category'] = $this->getProductCategory($product);
        }
        
        return $params;
    }
    
    /**
     * Get product category
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getProductCategory($product)
    {
        $categories = $product->getCategoryIds();
        if (!empty($categories)) {
            // Return first category name
            return 'Category ' . $categories[0]; // Simplified
        }
        return 'Uncategorized';
    }
}