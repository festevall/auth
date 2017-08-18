<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.17
 * Time: 12:13
 */

namespace Provider;


use Model\ModelInterface;
use Silex\Application;
use Model\User as User;


class FileDataBase extends AbstractDataBase
{

    /**
     * @var string
     */
    private $_operatedFileData;

    /**
     * @var string
     */
    private $_storageFile;

    /**
     * FileDataBase constructor.
     * @param Application $app
     * @param $class
     */
    public function __construct(Application $app, $class) {
        parent::__construct($app, $class);
        $this->_storageFile = $this->_storageFilePath . $this->_storageFileName . $this->_storageFileExtension;
    }

    /**
     * @param $id
     * @return User
     */
    public function find($id) {
        $this->_operatedFileData = explode("\n", file_get_contents($this->_storageFile));

        foreach ($this->_operatedFileData as $row) {

            $line = json_decode($row);

            if($line->_id === $id) {
                $nameSpace = 'Model\\' . $this->_modelClass;
                $userData = json_decode($row, true);
                return new $nameSpace($userData);
            }

        }

        return null;
    }

    /**
     * @param array $criteria
     * @return User
     */
    public function findOne(array $criteria) {
        $this->_operatedFileData = explode("\n", file_get_contents($this->_storageFile));

        foreach ($criteria as $criteriaKey => $criteriaValue) {
            foreach ($this->_operatedFileData as $row) {
                $line = json_decode($row);

                if( isset($line->{$criteriaKey}) && $line->{$criteriaKey} == $criteriaValue ) {
//                    var_dump($this->_modelClass);die;
                    $nameSpace = 'Model\\' . $this->_modelClass;
                    $userData = json_decode($row, true);
                    return new $nameSpace($userData);
                }
        }
        }

        return null;
    }

    /**
     * @param ModelInterface $model
     */
    public function persist(ModelInterface $model) {
        $this->_model = $model;
    }

    /**
     * @return bool|int
     */
    public function flush() {
        if(!empty($this->_model)) {
            return file_put_contents($this->_storageFile, $this->_model->__toString() . "\n", FILE_APPEND | LOCK_EX);
        }

        return false;
    }

}