<?php

require_once (__DIR__ . '/getlist.class.php');

class customMsProductGetListProcessor extends msProductGetListProcessor
{
    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->where(array('class_key' => 'msProduct'));
        $c->leftJoin('msProductData', 'Data', 'msProduct.id = Data.id');
        $c->leftJoin('msCategoryMember', 'Member', 'msProduct.id = Member.product_id');
        $c->leftJoin('msProductsSort', 'Sorting', ['msProduct.id = Sorting.sortproduct_id', 'Sorting.sortcategory_id = ' . $this->getProperty('parent')]);
        $c->leftJoin('msVendor', 'Vendor', 'Data.vendor = Vendor.id');
        $c->leftJoin('msCategory', 'Category', 'Category.id = msProduct.parent');
        if ($this->getProperty('combo')) {
            $c->select('msProduct.id,msProduct.pagetitle,msProduct.context_key');
        } else {
            $c->select($this->modx->getSelectColumns('msProduct', 'msProduct'));
            $c->select($this->modx->getSelectColumns('msProductData', 'Data', '', array('id'), true));
            $c->select($this->modx->getSelectColumns('msVendor', 'Vendor', 'vendor_', array('name')));
            $c->select($this->modx->getSelectColumns('msCategory', 'Category', 'category_', array('pagetitle')));
            $c->select($this->modx->getSelectColumns('msProductsSort', 'Sorting','', ['sortproduct_idx','sortproduct_id','sortcategory_id'], false));
        }
        if ($this->item_id) {
            $c->where(array('msProduct.id' => $this->item_id));
            if ($parent = (int)$this->getProperty('parent')) {
                $this->parent = $parent;
            }
        } else {
            $query = trim($this->getProperty('query'));
            if (!empty($query)) {
                if (is_numeric($query)) {
                    $c->where(array(
                        'msProduct.id' => $query,
                        'OR:Data.article:=' => $query,
                    ));
                } else {
                    $c->where(array(
                        'msProduct.pagetitle:LIKE' => "%{$query}%",
                        'OR:msProduct.longtitle:LIKE' => "%{$query}%",
                        'OR:msProduct.description:LIKE' => "%{$query}%",
                        'OR:msProduct.introtext:LIKE' => "%{$query}%",
                        'OR:Data.article:LIKE' => "%{$query}%",
                        'OR:Data.made_in:LIKE' => "%{$query}%",
                        'OR:Vendor.name:LIKE' => "%{$query}%",
                        'OR:Category.pagetitle:LIKE' => "%{$query}%",
                    ));
                }
            }

            $parent = (int)$this->getProperty('parent');
            if (!empty($parent)) {
                $category = $this->modx->getObject('modResource', $parent);
                $this->parent = $parent;
                $parents = array($parent);

                $nested = $this->getProperty('nested', null);
                $nested = $nested === null && $this->modx->getOption('ms2_category_show_nested_products', null, true)
                    ? true
                    : (bool)$nested;
                if ($nested) {
                    $tmp = $this->modx->getChildIds($parent, 10, array('context' => $category->get('context_key')));
                    foreach ($tmp as $v) {
                        $parents[] = $v;
                    }
                }
                $parents= "(" . implode(',', $parents) . ")";
                $c->query['where'][] = [[
                    new xPDOQueryCondition(array('sql' => 'msProduct.parent IN '.$parents, 'conjunction' => 'OR')),
                    new xPDOQueryCondition(array('sql' => 'Member.category_id IN '.$parents, 'conjunction' => 'OR'))
                ]];
            }
        }

        $c->groupby($this->classKey . '.id');

        return $c;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $c = $this->prepareQueryAfterCount($c);
        $data = [
            'results' => ($c->prepare() AND $sql = $c->toSQL() AND $c->stmt->execute()) ? $c->stmt->fetchAll(PDO::FETCH_ASSOC) : [],
            'total'   => (int)$this->getProperty('total'),
        ];
        //$this->modx->log(1, print_r($data,1));
        //$this->modx->log(1, $sql);

        return $data;
    }
}

return 'customMsProductGetListProcessor';
