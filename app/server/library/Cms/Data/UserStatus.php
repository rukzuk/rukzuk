<?php
namespace Cms\Data;

class UserStatus
{
    /**
     * @var string $userId
     */
    private $userId;

    /**
     * @var string $authBackend
     */
    private $authBackend;

    /**
     * @var int $lastLogin
     */
    private $lastLogin;

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getAuthBackend()
    {
        return $this->authBackend;
    }

    /**
     * @param string $authBackend
     */
    public function setAuthBackend($authBackend)
    {
        $this->authBackend = $authBackend;
    }

    /**
     * @return \DataTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param \DataTime $lastLogin
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * to array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'userId'      => $this->getUserId(),
            'authBackend' => $this->getAuthBackend(),
            'lastLogin'   => $this->getLastLogin(),
        );
    }
}
