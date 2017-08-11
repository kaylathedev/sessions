<?php
namespace WaffleSystems\Session;

use WaffleSystems\IO\StorageInterface;

class BasicSessionManager implements SessionInterface
{

    private $started;
    private $manager;
    private $session;
    private $domain;
    private $path;
    private $httpOnly;
    private $secureOnly;

    public function __construct(StorageInterface $storage, $expiresInSeconds = 86400)
    {
        $this->manager = new SessionManager($storage, $expiresInSeconds);
    }

    /**
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $value
     */
    public function setCookieDomain($value)
    {
        $this->domain = $value;
    }

    /**
     * @return string
     */
    public function getCookiePath()
    {
        return $this->path;
    }

    /**
     * @param string $value
     */
    public function setCookiePath($value)
    {
        $this->path = $value;
    }

    /**
     * @return boolean
     */
    public function isCookieHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @param boolean $value
     */
    public function setCookieHttpOnly($value)
    {
        $this->httpOnly = $value;
    }

    /**
     * @return boolean
     */
    public function isCookieSecureOnly()
    {
        return $this->secureOnly;
    }

    /**
     * @param boolean $value
     */
    public function setCookieSecureOnly($value)
    {
        $this->secureOnly = $value;
    }

    /**
     * @return mixed
     */
    public function get($path = null, $default = null)
    {
        if (!$this->started) {
            $this->initalizeSession();
        }
        return $this->session->get($path, $default);
    }

    public function set($path, $value)
    {
        if (!$this->started) {
            $this->initalizeSession();
        }
        $this->session->set($path, $value);
    }

    public function delete($path)
    {
        if (!$this->started) {
            $this->initalizeSession();
        }
        $this->session->delete($path);
    }

    /**
     * @return boolean
     */
    public function has($path)
    {
        if (!$this->started) {
            $this->initalizeSession();
        }
        return $this->session->has($path);
    }

    public function clear()
    {
        if (!$this->started) {
            $this->initalizeSession();
        }
        $this->session->clear();
    }

    private function initalizeSession()
    {
        $this->started = true;

        $cookie = null;

        if (isset($_COOKIE['session_id'])) {
            $cookie = $_COOKIE['session_id'];
            if (!$this->manager->has($cookie)) {
                $cookie = null;
            }
        }

        $this->session = $this->manager->load($cookie);
        setcookie(
            'session_id',
            $this->session->getId(),
            $this->session->getExpiresTime(),
            $this->path,
            $this->domain,
            $this->secureOnly,
            $this->httpOnly
        );
    }
    
    /**
     * @return string
     */
    public function getId()
    {
        if (!$this->started) {
            $this->initalizeSession();
        }
        return $this->session->getId();
    }

    /**
     * @return Session
     */
    public function getCurrentSession()
    {
        if (!$this->started) {
            $this->initalizeSession();
        }
        return $this->session;
    }

    public function unload()
    {
        if ($this->started) {
            $this->started = false;
            $this->manager->unload($this->session);
        }
    }
}
