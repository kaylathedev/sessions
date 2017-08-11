<?php
namespace WaffleSystems\Session;

use \RuntimeException;
use \WaffleSystems\IO\StorageInterface;

class SessionManager
{

    private $currentTime;
    private $expiresIn;
    private $storage;
    private $uniqueIdCallable;

    public function setUniqueIdCallback($callback)
    {
        $this->uniqueIdCallable = $callback;
    }

    public function __construct(StorageInterface $storage, $expiresInSeconds = 86400)
    {
        $this->currentTime      = time();
        $this->expiresIn        = $expiresInSeconds;
        $this->storage          = $storage;
        $this->uniqueIdCallable = function() {
            return uniqid('', true);
        };
    }

    public function load($sessionId = null)
    {
        if (null !== $sessionId) {
            $data = $this->storage->get($sessionId);
            if (null !== $data && $data['expires'] > $this->currentTime) {
                return new Session($sessionId, $data['expires'], $data['userData']);
            }
        }
        if (null === $sessionId) {
            $sessionId = $this->getUnusedId();
        }

        return new Session($sessionId, $this->currentTime + $this->expiresIn, []);
    }

    public function delete(Session $session)
    {
        $this->storage->delete($session->getId());
    }

    public function has($sessionId)
    {
        return $this->storage->has($sessionId);
    }

    public function invalidateId(Session &$session)
    {
        if ($this->storage->has($session->getId())) {
            $this->storage->delete($session->getId());
        }
        $session = new Session($this->getUnusedId(), $session->getExpiresTime(), $session->getUserData());
    }

    public function unload(Session $session)
    {
        if (count($session->getUserData()) > 0) {
            $this->storage->set($session->getId(), [
                'expires' => $session->getExpiresTime(),
                'userData' => $session->getUserData()
            ]);
        } else {
            $this->storage->delete($session->getId());
        }
    }

    private function getUnusedId()
    {
        do {
            $sessionId = call_user_func($this->uniqueIdCallable);
        } while ($this->storage->has($sessionId));
        return $sessionId;
    }
}
