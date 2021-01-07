<?php
namespace Trootech\General\Block;
use Magento\Framework\View\Element\Template;
 
class Navigation extends \Magento\Framework\View\Element\Template
{
	protected $_categoryFactory;
    protected $_categoryHelper;
	protected $_categoryCollection;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_categoryCollection,
		\Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Helper\Category $categoryHelper,   
        array $data = []
    )
    {
		$this->_categoryFactory = $categoryFactory;
		$this->_categoryHelper = $categoryHelper;
		$this->__categoryCollection = $_categoryCollection;
		$this->layerResolver = $layerResolver;		
        parent::__construct($context, $data);
    }
 
	public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    public function getCurrentCategoryId()
    {
        return $this->getCurrentCategory()->getId();
    }
 
	public function getCategory($categoryId) 
    {
        $this->_category = $this->_categoryFactory->create();
        $this->_category->load($categoryId);        
        return $this->_category;
    }
	
	public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true) 
    {
        return $this->_categoryHelper->getStoreCategories();
    }
	
	public function getCategories()
	{
		return $this->__categoryCollection->create()->addAttributeToSelect('*')->addLevelFilter(2)->addAttributeToFilter('entity_id', array('neq' => 2))->setStore($this->_storeManager->getStore());
	}
}
