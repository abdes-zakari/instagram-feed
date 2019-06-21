# Instagram-feed
Get posts from Instgram page whitout Access Token.


## Installation 
```bash
composer require abdes/instagram-feed
```

## Usage 

					
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$username = ['youtube','anjunadeep']; //instagram Username
$num = "4"; //number of posts

$posts = new Instaty\InstaFeed($username,$num);

$posts = $posts->getFeeds();

echo "<pre>";print_r($posts);

```
