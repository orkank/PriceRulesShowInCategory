<?php
namespace IDangerous\PriceRulesShowInCategory\Block\Adminhtml\Product\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\CategoryFactory;

class PriceRules extends Template
{
    /**
     * @var string
     */
    protected $_template = 'IDangerous_PriceRulesShowInCategory::product/tab/price_rules.phtml';

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
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $ruleCollectionFactory
     * @param PriceHelper $priceHelper
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $ruleCollectionFactory,
        PriceHelper $priceHelper,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->priceHelper = $priceHelper;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * @return array
     */
    public function getPriceRules()
    {
        $product = $this->getProduct();
        if (!$product || !$product->getId()) {
            return [];
        }

        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);

        $rules = [];
        foreach ($collection as $rule) {
            $conditions = $rule->getConditions()->asArray();
            if ($this->checkProductMatchesConditions($conditions, $product)) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * @param array $conditions
     * @param Product $product
     * @return bool
     */
    protected function checkProductMatchesConditions($conditions, $product)
    {
        if (empty($conditions)) {
            return true; // No conditions means rule applies to all products
        }

        if (isset($conditions['conditions'])) {
            // Default aggregator is usually 'all' (AND logic)
            $aggregator = isset($conditions['aggregator']) ? $conditions['aggregator'] : 'all';

            if ($aggregator === 'all') {
                // ALL conditions must be true (AND logic)
                foreach ($conditions['conditions'] as $condition) {
                    $conditionResult = false;

                    if (!empty($condition['conditions'])) {
                        // Nested conditions
                        $conditionResult = $this->checkProductMatchesConditions($condition, $product);
                    } else {
                        // Single condition
                        $conditionResult = $this->evaluateCondition($condition, $product);
                    }

                    if (!$conditionResult) {
                        return false; // If any condition fails, rule doesn't apply
                    }
                }
                return true; // All conditions passed
            } else {
                // ANY condition must be true (OR logic)
                foreach ($conditions['conditions'] as $condition) {
                    $conditionResult = false;

                    if (!empty($condition['conditions'])) {
                        // Nested conditions
                        $conditionResult = $this->checkProductMatchesConditions($condition, $product);
                    } else {
                        // Single condition
                        $conditionResult = $this->evaluateCondition($condition, $product);
                    }

                    if ($conditionResult) {
                        return true; // If any condition passes, rule applies
                    }
                }
                return false; // No condition passed
            }
        }

        return false;
    }

    /**
     * Evaluate a single condition against the product
     *
     * @param array $condition
     * @param Product $product
     * @return bool
     */
    protected function evaluateCondition($condition, $product)
    {
        if (!isset($condition['attribute']) || !isset($condition['operator'])) {
            return false;
        }

        $attribute = $condition['attribute'];
        $operator = $condition['operator'];
        $value = isset($condition['value']) ? $condition['value'] : '';

        // Special handling for category_ids attribute
        if ($attribute === 'category_ids') {
            return $this->evaluateCategoryCondition($product, $operator, $value);
        }

        // Get product attribute value
        $productValue = $this->getProductAttributeValue($product, $attribute);

        // Evaluate based on operator
        switch ($operator) {
            case '==':
                return $productValue == $value;
            case '!=':
                return $productValue != $value;
            case '>=':
                return (float)$productValue >= (float)$value;
            case '<=':
                return (float)$productValue <= (float)$value;
            case '>':
                return (float)$productValue > (float)$value;
            case '<':
                return (float)$productValue < (float)$value;
            case '{}':
                return strpos($productValue, $value) !== false;
            case '!{}':
                return strpos($productValue, $value) === false;
            case '()':
                $values = explode(',', $value);
                return in_array($productValue, $values);
            case '!()':
                $values = explode(',', $value);
                return !in_array($productValue, $values);
        }

        return false;
    }

    /**
     * Evaluate category condition specifically
     *
     * @param Product $product
     * @param string $operator
     * @param string $value
     * @return bool
     */
    protected function evaluateCategoryCondition($product, $operator, $value)
    {
        $productCategoryIds = $product->getCategoryIds();
        $conditionCategoryIds = explode(',', $value);

        switch ($operator) {
            case '==':
                // Product must be in ALL specified categories
                foreach ($conditionCategoryIds as $categoryId) {
                    if (!in_array(trim($categoryId), $productCategoryIds)) {
                        return false;
                    }
                }
                return true;

            case '!=':
                // Product must NOT be in ANY of the specified categories
                foreach ($conditionCategoryIds as $categoryId) {
                    if (in_array(trim($categoryId), $productCategoryIds)) {
                        return false;
                    }
                }
                return true;

            case '()':
                // Product must be in at least ONE of the specified categories
                foreach ($conditionCategoryIds as $categoryId) {
                    if (in_array(trim($categoryId), $productCategoryIds)) {
                        return true;
                    }
                }
                return false;

            case '!()':
                // Product must NOT be in ANY of the specified categories
                foreach ($conditionCategoryIds as $categoryId) {
                    if (in_array(trim($categoryId), $productCategoryIds)) {
                        return false;
                    }
                }
                return true;

            case '{}':
                // Product categories contain the specified category
                foreach ($conditionCategoryIds as $categoryId) {
                    if (in_array(trim($categoryId), $productCategoryIds)) {
                        return true;
                    }
                }
                return false;

            case '!{}':
                // Product categories do NOT contain any of the specified categories
                foreach ($conditionCategoryIds as $categoryId) {
                    if (in_array(trim($categoryId), $productCategoryIds)) {
                        return false;
                    }
                }
                return true;
        }

        return false;
    }

    /**
     * Get product attribute value
     *
     * @param Product $product
     * @param string $attribute
     * @return mixed
     */
    protected function getProductAttributeValue($product, $attribute)
    {
        switch ($attribute) {
            case 'category_ids':
                return implode(',', $product->getCategoryIds());
            case 'price':
                return $product->getPrice();
            case 'special_price':
                return $product->getSpecialPrice();
            case 'attribute_set_id':
                return $product->getAttributeSetId();
            case 'sku':
                return $product->getSku();
            case 'status':
                return $product->getStatus();
            case 'visibility':
                return $product->getVisibility();
            default:
                return $product->getData($attribute);
        }
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
            'sku' => __('SKU'),
            'status' => __('Status'),
            'visibility' => __('Visibility'),
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
            return $this->getCategoryNamesById($condition['value']);
        }

        return $condition['value'];
    }

    /**
     * Get category names by IDs
     *
     * @param string $categoryIds
     * @return string
     */
    protected function getCategoryNamesById($categoryIds)
    {
        $categoryIdArray = explode(',', $categoryIds);
        $categoryNames = [];

        foreach ($categoryIdArray as $categoryId) {
            $categoryId = trim($categoryId);
            if ($categoryId) {
                $category = $this->categoryFactory->create()->load($categoryId);
                if ($category->getId()) {
                    $categoryNames[] = $category->getName() . ' (ID: ' . $categoryId . ')';
                } else {
                    $categoryNames[] = __('Category ID: %1 (Not Found)', $categoryId);
                }
            }
        }

        return implode(', ', $categoryNames);
    }
}