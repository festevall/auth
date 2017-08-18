<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.17
 * Time: 12:07
 */

namespace Model;


class User implements \JsonSerializable, ModelInterface
{

    const USER_AUTH_KEY = 'auth_authorization';

    const USER_ID_KEY = 'user_identifier';

    /**
     * @var string
     */
    private $_id;

    /**
     * @var string
     */
    private $_login;

    /**
     * @var string
     */
    private $_password;

    /**
     * @var string
     */
    private $_salt;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var string
     */
    private $_email;

    /**
     * User constructor.
     * @param array|null $properties
     */
    public function __construct(array $properties = null) {
        if(!empty($properties)) {
            foreach ($properties as $key => $value) {
                if($key != 'id' && in_array($key, array_keys(get_object_vars($this)))) {
                    $this->{$key} = ($value);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @param $login
     * @return User
     */
    public function setLogin($login) {
        $this->_login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin() {
        return $this->_login;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password) {
        $this->_password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->_password;
    }

    /**
     * @param $salt
     * @return $this
     */
    public function setSalt($salt) {
        $this->_salt = $salt;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalt() {
        return $this->_salt;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name) {
        $this->_name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email) {
        $this->_email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->_email;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array
     */
    public function __toArray() {
        return $this->jsonSerialize();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

}