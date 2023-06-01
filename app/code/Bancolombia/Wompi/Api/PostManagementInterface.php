<?php
namespace Bancolombia\Wompi\Api;
interface PostManagementInterface {
    /**
     * GET for Post api
     * @param string $storeid
     * @param string $name
     * @param string $city
     * @return string
     */
    public function customPostMethod();
}