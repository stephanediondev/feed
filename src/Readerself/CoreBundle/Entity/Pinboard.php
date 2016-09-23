<?php
namespace Readerself\CoreBundle\Entity;

class Pinboard
{
    /**
     * @var string
     */
    private $token;

    /**
     * Set token
     *
     * @param string $token
     *
     * @return Pinboard
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
