<?php

// Создание записей msproductssort в таблице
// Запускать в консоли

$q = $modx->newQuery('modResource');
$q->select('modResource.id');
$q->where([
    'modResource.class_key' => 'msProduct'
]);
$q->prepare();
$sql = $q->toSQL();
$q->stmt->execute();
$products = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

$n = 0;
foreach($products as $key => $product) {
    $n++;
    $sorting = new createMsProductsSort($modx, $product['id']);
    $result = $sorting->createAllProductIdx();
    unset($sorting);
}

echo "Сгенерировано $n записей в таблице ms2_products_sort";

// $sorting = new createMsProductsSort($modx, 74);
// echo $sorting->createOneProductIdx(32, 9999);
