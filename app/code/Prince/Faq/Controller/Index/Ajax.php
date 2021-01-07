<?php
namespace Prince\Faq\Controller\Index;

class Ajax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Prince\Faq\Block\Index\Index $faq_block
    ) {
		$this->faq_block = $faq_block;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		try{
			$cat_id = $this->getRequest()->getParam("cat_id",0);
			$searchQuery = trim($this->getRequest()->getParam("query",""));
			$faqCollection = false;
			if($cat_id){
				$faqCollection = $this->faq_block->getFaqCollection($cat_id);
			}
			if($searchQuery){
				$faqCollection = $this->faq_block->getFaqCollection();
				$faqCollection->addFieldToFilter(
									array('title','content'),
									array(
										array('like'=>'%'.$searchQuery.'%'),
										array('like'=>'%'.$searchQuery.'%')
									)
								);
			}
			if($faqCollection && $faqCollection->getSize()){
				$html = '';
				foreach($faqCollection as $faq){
					$html .= '<div class="card"><div class="card-header" role="tab" id="heading'.$faq->getId().'"><h5 class="mb-0">';						
					$html .= '<a class="collapsed" data-toggle="collapse" href="#collapse'.$faq->getId().'" aria-expanded="false" aria-controls="collapse'.$faq->getId().'">'.$faq->getTitle().'</a>';
                    $html .= '</h5></div><div id="collapse'.$faq->getId().'" class="collapse" role="tabpanel" aria-labelledby="heading'.$faq->getId().'" data-parent="#accordion"><div class="card-body">';
					$html .= '<div>'.$this->faq_block->filterOutputHtml($faq->getContent()).'</div>';
					$html .= '</div></div></div>';			
				}
				echo $html;
			} else {
				/* return $this->resultPageFactory->create(); */
				echo __("No Records available.");
			}
			exit;
		} catch(\Exception $e){
			echo __("No Records available.");
		}
    }
}
