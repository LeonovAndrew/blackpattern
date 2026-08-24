<?php
$arUrlRewrite=array (
  0 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/journal/([a-z0-9_-]+)/?(?:\?.*)?$#',
    'RULE' => 'article=$1',
    'ID' => 'bitrix:news',
    'PATH' => '/journal/index.php',
    'SORT' => 100,
  ),
  2 =>
  array (
    'CONDITION' => '#^/robots\\.txt$#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/robots.php',
    'SORT' => 50,
  ),
  3 =>
  array (
    'CONDITION' => '#^/sitemap\\.xml$#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/sitemap.php',
    'SORT' => 50,
  ),
);
