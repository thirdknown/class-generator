# PHP Class code generator (BETA with basic functionality)

Generate class code from array or JSON

## Installation via Docker

`git clone ...`

`cd ...`

`cp docker/conf/docker-compose.yml.dist docker-compose.yml`

`sudo docker-compose up -d`

## Running

Copy source JSON `order.json` file to `temp` directory.

`order.json`

```json
{
  "items": [
	{
	  "id": 1,
	  "quantity": 4,
	  "price": 33.98,
	  "name": "Chair"
  	},
	{
	  "id": 53,
	  "quantity": 1,
	  "price": 32.00,
	  "name": "Table"
	}
  ],
  "customer_first_name": "Paul",
  "customer_last_name": "Johson",
  "billing_address": {
	"street": "29th AVENUE",
	"city": "New York",
	"country": "US"
  },
  "registered_customer": false
}

```

Run:

`php ./console.php php ./console.php generate:classes-from-json temp My\\Namespace Order temp/order.json`

Then you will find 3 new files in `temp` directory.
**You can add getters and setters and change imports in PHP files by your IDE.**

### Generated files

`Order.php`

```php
<?php

declare(strict_types=1);

namespace Eshop\Order;

class Order
{
    /** @var Item[] */
    private array $items;

    private string $customerFirstName;

    private string $customerLastName;

    private \BillingAddress $billingAddress;

    private bool $registeredCustomer;
}
```

`Item.php`

```php
<?php

declare(strict_types=1);

namespace Eshop\Order;

class Item
{
    private int $id;

    private int $quantity;

    private float $price;

    private string $name;
}
```

`BillingAddress.php`

```php
<?php

declare(strict_types=1);

namespace Eshop\Order;

class BillingAddress
{
    private string $street;

    private string $city;

    private string $country;
}

```

