<?php
return [
  'routes' => [
    ['name' => 'user#index', 'url' => '/users', 'verb' => 'GET'],
    ['name' => 'user#getDetails', 'url' => '/users/{uid}/details', 'verb' => 'GET'],
    ['name' => 'user#create', 'url' => '/users', 'verb' => 'POST'],
    ['name' => 'user#disable', 'url' => '/users/{uid}/disable', 'verb' => 'POST'],
    ['name' => 'user#enable', 'url' => '/users/{uid}/enable', 'verb' => 'POST'],
    ['name' => 'user#delete', 'url' => '/users/{uid}', 'verb' => 'DELETE'],

    ['name' => 'create#createUser', 'url' => '/users/create', 'verb' => 'POST'],
    ['name' => 'create#getQuotaOptions', 'url' => '/users/quotas', 'verb' => 'GET'],

  ]
];
