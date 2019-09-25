<?php

require_once(__DIR__ . '/updatefromgrid.class.php');

class customMsProductUpdateFromGridProcessor extends msProductUpdateFromGridProcessor
{
    /**
     * @param modX $modx
     * @param string $className
     * @param array $properties
     *
     * @return modProcessor
     */
    public static function getInstance(modX &$modx, $className, $properties = array())
    {

        /** @var modProcessor $processor */
        $processor = new customMsProductUpdateFromGridProcessor($modx, $properties);

        return $processor;
    }

    /**
     * @return array|string
     */
    public function cleanup()
    {
        $this->object->removeLock();
        $this->clearCache();

        /** @var miniShop2 $miniShop2 */
        $miniShop2 = $this->modx->getService('miniShop2');
        /** @var modProcessorResponse $res */
        $res = $miniShop2->runProcessor('mgr/product/customgetlist', array(
            'id' => $this->object->id,
            'parent' => $this->object->toArray()['sortcategory_id']             // передача родителя открытой категории
        ));
        return $res->getResponse();
    }
}

return 'customMsProductUpdateFromGridProcessor';
