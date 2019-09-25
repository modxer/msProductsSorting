<?php

/**
 * Добавление композитных связей товарам и категориям
 * При удалении товаров или категорий - удаляются записи из m2_products_sort
 */

if($modx->event->name == 'OnMODXInit') {
    $modx->loadClass('msProduct');
    $modx->map['msProduct']['composites']['Sorting'] = array(
        'class' => 'msProductsSort',
        'local' => 'id',
        'foreign' => 'sortproduct_id',
        'cardinality' => 'many',
        'owner' => 'local',
    );
    $modx->loadClass('msCategory');
    $modx->map['msCategory']['composites']['Sorting'] = array(
        'class' => 'msProductsSort',
        'local' => 'id',
        'foreign' => 'sortcategory_id',
        'cardinality' => 'many',
        'owner' => 'local',
    );

    require_once (MODX_CORE_PATH . 'components/msproductssort/model/msproductssort/createmsproductssort.class.php');
}

/**
 * Кастомизация таблицы товаров в категории
 */

if($modx->event->name == 'msOnManagerCustomCssJs') {
    if ($page != 'category_update') return;
    $folder = MODX_ASSETS_URL . 'components/msproductssort/js/mgr/';

    $modx->regClientStartupHTMLBlock("<script src='{$folder}product.grid.js'></script>");
    $modx->controller->addLastJavascript($folder . 'default.js');
}

/**
 * Обновление индекса сортировки у товара при сохранении
 */

if($modx->event->name == 'OnBeforeDocFormSave') {
    $res = $resource->toArray();

    if($res['class_key']=='msProduct') {
        if($res['sortproduct_id'] and $res['sortcategory_id']) {
            $sorting = new createMsProductsSort($modx, $res['sortproduct_id']);
            $sorting->createOneProductIdx($res['sortcategory_id'], $res['sortproduct_idx']);
            unset($sorting);
        }
    }
}

/*
 * Создание индексов сортировки в БД если товар только создан
 */

if($modx->event->name == 'OnDocFormSave') {
    $res = $resource->toArray();

    if($res['class_key']=='msProduct') {
        if (!$res['sortproduct_id'] or !$res['sortcategory_id']) {
            $sorting = new createMsProductsSort($modx, $res['id']);
            $sorting->createAllProductIdx();
            unset($sorting);
        }
    }
}
