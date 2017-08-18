<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.17
 * Time: 12:23
 */

namespace Provider;


use Model\ModelInterface;
use Silex\Application;

abstract class AbstractDataBase implements DataBaseProviderInterface
{

    const STORAGE_FILE_EXTENSION = '.dat';

    /**
     * @var ModelInterface
     */
    protected $_model;

    /**
     * @var String
     */
    protected $_modelClass;

    /**
     * @var string
     */
    protected $_storageFilePath;

    /**
     * @var string
     */
    protected $_storageFileName;

    /**
     * @var string
     */
    protected $_storageFileExtension = self::STORAGE_FILE_EXTENSION;

    /**
     * AbstractDataBase constructor.
     * @param Application $app
     * @param $modelClass
     */
    public function __construct(Application $app, $modelClass) {
        $this->_modelClass = $modelClass;
        $this->_storageFilePath = $app['web_dir'] . '/../runtime/data/';
        $this->_storageFileName = $modelClass;
    }

}