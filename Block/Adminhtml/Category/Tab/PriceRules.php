<?php
namespace IDangerous\PriceRulesShowInCategory\Block\Adminhtml\Category\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class PriceRules extends Template
{
    /**
     * @var string
     */
    protected $_template = 'IDangerous_PriceRulesShowInCategory::category/tab/price_rules.phtml';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $ruleCollectionFactory
     * @param PriceHelper $priceHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $ruleCollectionFactory,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->coreRegistry->registry('category');
    }

    /**
     * @return array
     */
    public function getPriceRules()
    {
        $categoryId = $this->getCategory()->getId();
        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);

        $rules = [];
        foreach ($collection as $rule) {
            $conditions = $rule->getConditions()->asArray();
            if ($this->checkCategoryInConditions($conditions, $categoryId)) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @param array $conditions
     * @param int $categoryId
     * @return bool
     */
    protected function checkCategoryInConditions($conditions, $categoryId)
    {
        if (empty($conditions)) {
            return false;
        }

        if (isset($conditions['conditions'])) {
            foreach ($conditions['conditions'] as $condition) {
                if ($condition['attribute'] == 'category_ids'
                    && in_array($categoryId, explode(',', $condition['value']))) {
                    return true;
                }

                if (!empty($condition['conditions'])) {
                    if ($this->checkCategoryInConditions($condition, $categoryId)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get price helper
     *
     * @return PriceHelper
     */
    public function getPriceHelper()
    {
        return $this->priceHelper;
    }
}