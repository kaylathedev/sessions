<?php
namespace WaffleSystems\Session;

interface SessionInterface
{

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function get($path, $default = null);

    /**
     * @param string $path
     *
     * @return void
     */
    public function set($path, $value);

    /**
     * @param string $path
     *
     * @return boolean
     */
    public function has($path);

    /**
     * @param string $path
     * 
     * @return void
     */
    public function delete($path);
}
