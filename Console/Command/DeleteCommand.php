<?php

namespace Angeldm\ProductsDelete\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ProductAlert data helper
 *
 * @author     Àngel Díaz <angeldm@gmail.com>
 *
 * @api
 * @since 2.0.0
 */
class DeleteCommand extends Command
{
    const NAME_OPTION = "categories";

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
        $this->_objectManager->get(Magento\Framework\Registry::class)->register('isSecureArea', true);
        parent::__construct();
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $option = $input->getOption(self::NAME_OPTION);

        $products= $this->deleteAllProducts($output);
        $output->writeln('<info>Deleted ' . $products . ' products</info>');
        if ($option) {
            $categories = $this->deleteAllCategories($output);
            $output->writeln('<info>Deleted ' . $categories . ' categories</info>');
        }
        $output->writeln("Hello ");
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("products:delete");
        $this->setDescription("delete");
        $this->setDefinition([
            new InputOption(self::NAME_OPTION, "-c", InputOption::VALUE_NONE, "Delete categories")
        ]);
        parent::configure();
    }

    private function deleteAllCategories(OutputInterface $output)
    {
        $categoryFactory = $this->_objectManager->get(Magento\Catalog\Model\CategoryFactory::class);
        $newCategory = $categoryFactory->create();
        $collection = $newCategory->getCollection();
        $i=0;
        foreach ($collection as $category) {
            if ($category->getId() > 2) {
                if ($output->isVerbose()) {
                    $output->writeln('Deleted: ' . $category->getName());
                }
                $category->delete();
                $i++;
            }
        }
        return $i;
    }

    private function deleteAllProducts(OutputInterface $output)
    {
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->load();
        $app_state = $this->_objectManager->get(Magento\Framework\App\State::class);
        $app_state->setAreaCode('frontend');
        $i=0;
        foreach ($collection as $product) {
            if ($output->isVerbose()) {
                $output->writeln('Deleted: ' . $product->getSku());
            }
            $this->_productRepository->deleteById($product->getSku());
            $i++;
        }
        return $i;
    }
}
