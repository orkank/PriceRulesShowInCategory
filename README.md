# Price Rules Show In Category and Product for Magento 2

This module adds a new tab in the Magento 2 admin category and product edit pages that displays all catalog price rules applied to the current category or product.

## Features

- **Displays a new "Price Rules" tab in category edit page**
- **Displays a new "Price Rules" fieldset in product edit page**
- **Shows all active catalog price rules that affect the current category or product**
- **Accordion Interface**: Each rule displayed as a collapsible tab for better organization
- **Enhanced User Experience**: Clean, professional interface with color-coded status indicators
- Displays comprehensive rule details including:
  - Rule Name
  - Description
  - Start Date
  - End Date
  - Discount Amount
  - Status (with color coding: 🟢 Active / 🔴 Inactive)
  - All Rule Conditions in human-readable format
- Supports multiple languages:
  - English (default)
  - Turkish (tr_TR)
- **Smart Condition Logic**: Correctly evaluates AND/OR logic for rule conditions
- **Category Name Display**: Shows actual category names instead of IDs
- Human-readable condition display showing:
  - Attribute names in user-friendly format
  - Operator descriptions in plain language
  - Category names with IDs (e.g., "Electronics (ID: 123)")
  - Formatted values for better readability
  - Nested conditions support with proper AND/OR logic

## Requirements

- Magento 2.3.x or higher
- PHP 7.2 or higher

## Installation

1. Create directory for the module:
```bash
mkdir -p app/code/IDangerous/PriceRulesShowInCategory
```

2. Copy module files to the directory

3. Enable the module:
```bash
php bin/magento module:enable IDangerous_PriceRulesShowInCategory
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

4. Clear the cache:
```bash
php bin/magento cache:clean
```

## Usage

### For Categories:
1. Log in to Magento Admin Panel
2. Navigate to **Catalog > Categories**
3. Select a category to edit
4. Look for the **"Price Rules"** tab
5. The tab will display all catalog price rules that affect the selected category

### For Products:
1. Log in to Magento Admin Panel
2. Navigate to **Catalog > Products**
3. Select a product to edit
4. Look for the **"Price Rules"** fieldset (collapsible section)
5. The fieldset will display all catalog price rules that affect the selected product

### Features:
- **Accordion Interface**: Click on any rule title to expand/collapse its details
- **Color-coded Status**: Active rules shown in green, inactive in red
- **Smart Rule Matching**: Only shows rules that actually apply to the category/product
- **Detailed Information**: Complete rule conditions, dates, and discount amounts
- **Category Names**: Shows actual category names instead of cryptic IDs

## Languages

The module includes translations for:
- English (default)
- Turkish (tr_TR)

To update translations after making changes:
```bash
php bin/magento setup:static-content:deploy tr_TR -f
php bin/magento cache:clean
```

## Changelog

### Version 2.0.0 (Latest)
- ✅ **NEW**: Extended module to support product edit pages
- ✅ **NEW**: Accordion interface for better organization when multiple rules exist
- ✅ **NEW**: Color-coded status indicators (Green for Active, Red for Inactive)
- ✅ **IMPROVED**: Shows category names instead of IDs for better readability
- ✅ **FIXED**: Correct AND/OR logic evaluation for complex rule conditions
- ✅ **IMPROVED**: Enhanced UI with professional styling and animations
- ✅ **IMPROVED**: All accordions start closed for cleaner initial view
- ✅ **IMPROVED**: Better condition evaluation for category-based rules

### Version 1.0.0 (Original)
- Basic category tab functionality
- Simple rule display
- Turkish translation support

## Technical Details

### Smart Condition Logic
The module correctly evaluates catalog price rule conditions using proper AND/OR logic:
- **AND Logic (default)**: ALL conditions must be satisfied for rule to apply
- **OR Logic**: ANY condition can be satisfied for rule to apply
- **Category Conditions**: Proper handling of category inclusion/exclusion logic

### UI Components
- **Categories**: Traditional tab interface
- **Products**: UI Component fieldset integration
- **Accordion**: JavaScript-powered collapsible interface
- **Responsive**: Works on all admin screen sizes

## License

[MIT License](LICENSE)

[Developer: Orkan Köylü](orkan.koylu@gmail.com)