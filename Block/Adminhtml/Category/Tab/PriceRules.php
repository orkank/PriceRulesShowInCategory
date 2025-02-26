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

    /**
     * Format rule conditions for display
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @return array
     */
    public function getFormattedConditions($rule)
    {
        $conditions = $rule->getConditions()->asArray();
        return $this->formatConditionsArray($conditions);
    }

    /**
     * Recursively format conditions array
     *
     * @param array $conditions
     * @return array
     */
    protected function formatConditionsArray($conditions)
    {
        $formatted = [];

        if (isset($conditions['conditions']) && is_array($conditions['conditions'])) {
            foreach ($conditions['conditions'] as $condition) {
                $conditionText = '';

                if (isset($condition['attribute'])) {
                    $conditionText = $this->getAttributeLabel($condition['attribute']) . ' ';
                    $conditionText .= $this->getOperatorLabel($condition['operator']) . ' ';
                    $conditionText .= $this->formatValue($condition);
                }

                if (!empty($conditionText)) {
                    $formatted[] = $conditionText;
                }

                if (!empty($condition['conditions'])) {
                    $childConditions = $this->formatConditionsArray($condition);
                    $formatted = array_merge($formatted, $childConditions);
                }
            }
        }

        return $formatted;
    }

    /**
     * Get human readable label for attribute
     *
     * @param string $attribute
     * @return string
     */
    protected function getAttributeLabel($attribute)
    {
        $labels = [
            'category_ids' => __('Category'),
            'attribute_set_id' => __('Attribute Set'),
            'price' => __('Price'),
            'special_price' => __('Special Price'),
            // Add more attribute labels as needed
        ];

        return isset($labels[$attribute]) ? $labels[$attribute] : __(ucwords(str_replace('_', ' ', $attribute)));
    }

    /**
     * Get human readable label for operator
     *
     * @param string $operator
     * @return string
     */
    protected function getOperatorLabel($operator)
    {
        $labels = [
            '==' => __('is'),
            '!=' => __('is not'),
            '>=' => __('equals or greater than'),
            '<=' => __('equals or less than'),
            '>' => __('greater than'),
            '<' => __('less than'),
            '{}' => __('contains'),
            '!{}' => __('does not contain'),
            '()' => __('is one of'),
            '!()' => __('is not one of'),
        ];

        return isset($labels[$operator]) ? $labels[$operator] : $operator;
    }

    /**
     * Format condition value for display
     *
     * @param array $condition
     * @return string
     */
    protected function formatValue($condition)
    {
        if ($condition['attribute'] == 'category_ids') {
            return implode(', ', explode(',', $condition['value']));
        }

        return $condition['value'];
    }
}