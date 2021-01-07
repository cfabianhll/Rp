<?php 
use Magento\Framework\App\Bootstrap; 
require DIR . '/../app/bootstrap.php'; 
$bootstrap = Bootstrap::create(BP, $_SERVER); 
$obj = $bootstrap->getObjectManager(); 
$state = $obj->get('Magento\Framework\App\State'); 
$state->setAreaCode('frontend');
$sku = 'iPhone-Xs';
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$product = $objectManager->create("Magento\Catalog\Model\Product")->loadByAttribute('sku', $sku); //use load($producID) if you have product id
$storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
$currentStoreId = $storeManager->getStore()->getId();
$category_model = $objectManager->get('Magento\Catalog\Model\Category');

$category_id = 2;//default category

$category = $category_model->load($category_id); 
// $subcategories = $category->getChildrenCategories();
// $subcat_array = array();
// foreach($subcategories as $key => $subcategory) {
// $subcat_array[$alphabet][$key]['name']= $subcategory->getName();
// $subcat_array[$alphabet][$key]['url']= $subcategory->getUrl();
// }
//echo $category->getChildrenCount();
?>
<?php if($category->getChildrenCount() > 0) { ?> 
<nav class="nav-drill">
<ul class="nav-items nav-level-1">
	<?php $childrenCategories = $category->getChildrenCategories();
	foreach ($childrenCategories as $_category): ?>
		<?php $_category = $category_model->load($_category->getId()); ?>
		<?php if($_category->getChildrenCount() > 0 ) { ?>		
			<li class="nav-item nav-expand">
				<a href="<?php echo $_category->getUrl() ?>" class="nav-link nav-expand-link"><?php echo $_category->getName(); ?></a>
				<?php $category = $category_model->load($_category->getId()); ?>
				<?php $childrenCategories = $category->getChildrenCategories();?>
				<ul class="nav-items nav-expand-content level-2">
					<?php foreach ($childrenCategories as $_category): ?>
						<?php $_category = $category_model->load($_category->getId()); ?>
						<?php if($_category->getChildrenCount() > 0 ) { ?>
							<li cass="nav-item nav-expand">
								<a class="nav-link nav-expand-link" href="<?php echo $_category->getUrl() ?>"><?php echo $_category->getName(); ?></a>
								
								<?php $category = $category_model->load($_category->getId()); ?>
								<?php $childrenCategories = $category->getChildrenCategories();?>
								<ul class="nav-items nav-expand-content level-3">
									<?php foreach ($childrenCategories as $_category): ?>
									<?php $_category = $category_model->load($_category->getId()); ?>
										<li class="nav-item">
											<a class="nav-link" href="<?php echo $_category->getUrl() ?>"><?php echo $_category->getName(); ?></a>
										</li>
									<?php endforeach; ?>
								</ul>
							
							</li>
						<?php } else { ?>
							<li class="nav-item">
								<a class="nav-link" href="<?php echo $_category->getUrl() ?>"><?php echo $_category->getName(); ?></a>
							</li>
						<?php } ?>
					<?php endforeach; ?>
				</ul>
			</li>
		<?php } else { ?>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo $_category->getUrl() ?>"> <span class="gw-menu-text"><?= $block->escapeHtml($_category->getName()) ?></span></a>
			</li>
		<?php } ?>
	<?php endforeach; ?>
</ul>
</nav>
<?php } ?>