<?php
namespace WaffleSystems\Session;

use \WaffleSystems\IO\ArrayDotNotation;

class Session implements SessionInterface
{

    private $id;
    private $expires;
    private $userData;

    /*
     * @param string $id
     * @param mixed $expires
     * @param array $userData
     */
    public function __construct($id, $expires, array $userData)
    {
        $this->id       = $id;
        $this->expires  = $expires;
        $this->userData = new ArrayDotNotation($userData);
    }

    /**
     * @return mixed
     */
    public function getExpiresTime()
    {
        return $this->expires;
    }

    public function getUserData()
    {
        return $this->userData->dump();
    }

    /**
     * @param string $path
     */
    public function get($path, $default = null)
    {
        return $this->userData->get($path, $default);
    }

    /**
     * @return void
     */
    public function set($path, $value)
    {
        $this->userData->set($path, $value);
    }

    /**
     * @return void
     */
    public function delete($path)
    {
        $this->userData->delete($path);
    }

    public function has($path)
    {
        return $this->userData->has($path);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->userData->clear();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
