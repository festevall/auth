<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.17
 * Time: 12:12
 */

namespace Provider;


use Model\ModelInterface;

interface DataBaseProviderInterface
{

    public function find($id);

    public function findOne(array $criteria);

    public function persist(ModelInterface $model);

    public function flush();

}