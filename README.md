# Price Rules Show In Category for Magento 2

This module adds a new tab in the Magento 2 admin category edit page that displays all catalog price rules applied to the current category.

## Features

- Displays a new "Price Rules" tab in category edit page
- Shows all active catalog price rules that affect the current category
- Displays rule details including:
  - Rule Name
  - Description
  - Start Date
  - End Date
  - Discount Amount
  - Status
- Supports multiple languages (includes Turkish translations)

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

1. Log in to Magento Admin Panel
2. Navigate to Catalog > Categories
3. Select a category to edit
4. Look for the "Price Rules" tab
5. The tab will display all catalog price rules that affect the selected category

## Languages

The module includes translations for:
- English (default)
- Turkish (tr_TR)

## License

[MIT License](LICENSE)

[Developer: Orkan Köylü](orkan.koylu@gmail.com)