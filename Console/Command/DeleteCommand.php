<?php


namespace Angeldm\ProductsDelete\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";

    protected $_objectManager;
    protected $_registry;
    protected $_productCollectionFactory;
    protected $_productRepository;

    public function __construct(
	    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
	    \Magento\Framework\Registry $registry
  
    ) {
	    $this->_registry = $registry;
	    $this->_productRepository = $productRepository;
	$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$this->_productCollectionFactory = $productCollectionFactory;
	parent::__construct();
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $name = $input->getArgument(self::NAME_ARGUMENT);
        $option = $input->getOption(self::NAME_OPTION);
	$this->deleteAllProducts();
	$this->deleteAllCategories();
	$output->writeln("Hello " . $name);

    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("products:delete");
        $this->setDescription("delete");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }

    private function deleteAllCategories() {
    	$categoryFactory = $this->_objectManager->get('Magento\Catalog\Model\CategoryFactory');
	$newCategory = $categoryFactory->create();
	$collection = $newCategory->getCollection();
	//$this->_registry->register("isSecureArea", true);
	$app_state = $this->_objectManager->get('Magento\Framework\App\State');
    	$app_state->setAreaCode('frontend');
	foreach($collection as $category) {
	        if($category->getId() > 2)
         $category->delete();
    	}
    }

    private function deleteAllProducts(){
///	$collection = $this->_productCollectionFactory->create();
//	$collection->addAttributeToSelect('*');
//	$collection->load();
 
	    //	$this->_registry->register("isSecureArea", true);
	$this->_objectManager->get('Magento\Framework\Registry')->register('isSecureArea', true);
       // $productCollection = $this->_objectManager->create('Magento\Catalog\Model\\Product\CollectionFactory');
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->load();
        $app_state = $this->_objectManager->get('Magento\Framework\App\State');
        $app_state->setAreaCode('frontend');

	foreach ($collection as $product){
        	 $this->_productRepository->deleteById($product->getSku());   
	}    
	
    }
}
