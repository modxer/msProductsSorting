<?php

class createMsProductsSort {
    private $modx;
    private $productId;
    private $parentIds;
    private $categoriesIds;
    private $mergeProductCategories;

    public function __construct(modX &$modx, $productId)
    {
        $this->modx =& $modx;
        $this->productId = $productId;
        $this->parentIds = $this->getParentsIds($productId);
        $this->categoriesIds = $this->getCategoriesIds($productId);

        //$this->modx->log(1, "__construct($productId): " . print_r($this->mergeProductCategories,1));

        // объединяем массив родителей и категорий товара
        $this->mergeProductCategories = array_unique(array_merge($this->parentIds, $this->categoriesIds));
        //$this->modx->log(1, "mergeProductCategories($productId): " . print_r($this->mergeProductCategories,1));
    }

    /**
     * @param $productId
     * @return array
     * Получаем id всех родительских категорий товара
     */
    public function getParentsIds($productId) {
        $allParentIds = [];
        $allParentIds = $this->modx->getParentIds($productId, 10, ['context' => 'web']);

        // getParentIds не работает сразу после создания товара, обходим этот момент
        if(!$allParentIds) {
            $productObj = $this->modx->getObject('modResource', $productId);
            if($productObj) {
                $allParentIds[] = $productObj->parent;
                $allParentIds = array_merge($allParentIds, $this->getParentsIds($productObj->parent));
            }
        }
        if(!$allParentIds) return [];

        $q = $this->modx->newQuery('modResource');
        $q->select('modResource.id');

        $where = [];
        $where['modResource.class_key'] = 'msCategory';
        $where['modResource.id:IN'] = $allParentIds;
        $q->where($where);

        $q->prepare();
        //$sql = $q->toSQL();
        $q->stmt->execute();
        $results = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        $parentsIds = [];
        foreach($results as $row) {
            $parentsIds[] = $row['id'];
        }
        //$this->modx->log(1, "getParentsIds($productId): " . print_r($parentsIds,1));
        return $parentsIds;
    }

    /**
     * @param $productId
     * @return array
     * Получаем все категории товара из таблицы ms2_product_categories (msCategoryMember)
     */
    public function getCategoriesIds($productId) {
        $q = $this->modx->newQuery('msCategoryMember');
        $q->select('msCategoryMember.category_id');

        $where = [];
        $where['msCategoryMember.product_id'] = $productId;
        $q->where($where);
        $q->prepare();
        //$sql = $q->toSQL();
        $q->stmt->execute();
        $results = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        $categoriesIds = [];
        foreach($results as $row) {
            $categoriesIds[] = $row['category_id'];
        }
        //$this->modx->log(1, "categoriesIds($productId): " . print_r($categoriesIds,1));
        return $categoriesIds;
    }

    /**
     * @return bool
     * Удаляем записи с отсутствующими категориями
     */
    public function deleteOldFields() {
        if(!$this->mergeProductCategories) return false;
        $q = $this->modx->newQuery('msProductsSort');
        $q->command('delete');
        $q->where(array(
            'sortproduct_id' => $this->productId,
            'sortcategory_id:NOT IN' => $this->mergeProductCategories
        ));
        $q->prepare();
        $q->stmt->execute();
        return true;
    }

    /**
     * @return bool
     * @internal param $productId
     * Создаём все idx-записи товара для каждой категории
     * Удаляем неактуальные
     */
    public function createAllProductIdx($productIdx = false) {
        $this->deleteOldFields();
        foreach($this->mergeProductCategories as $categoryId) {
            $category = $this->modx->getObject('msProductsSort', [
                'sortproduct_id' => $this->productId,
                'sortcategory_id' => $categoryId
            ]);
            if($category and ($productIdx !== false)) {
                $category->set('sortproduct_idx', $productIdx);
                $category->save();
            }
            if(!$category) {
                $category = $this->modx->newObject('msProductsSort');
                $category->set('sortproduct_id', $this->productId);
                $category->set('sortcategory_id', $categoryId);
                $category->set('sortproduct_idx', $productIdx);
                $category->save();
            }
        }
        return true;
    }

    /**
     * @param $categoryId
     * @param $productIdx
     * @return bool
     * Создаём одну запись
     */
    public function createOneProductIdx($categoryId, $productIdx = false) {
        $this->deleteOldFields();

        $category = $this->modx->getObject('msProductsSort', [
            'sortproduct_id' => $this->productId,
            'sortcategory_id' => $categoryId
        ]);

        if($category and ($productIdx !== false)) {
            $category->set('sortproduct_idx', $productIdx);
            $category->save();
        } elseif(!$category) {
            $category = $this->modx->newObject('msProductsSort');
            $category->set('sortproduct_id', $this->productId);
            $category->set('sortcategory_id', $categoryId);
            $category->set('sortproduct_idx', $productIdx);
            $category->save();
        }
        if($category) {
            return true;
        } else {
            return false;
        }
    }
}
