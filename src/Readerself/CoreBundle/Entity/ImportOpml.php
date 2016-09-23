<?php
namespace Readerself\CoreBundle\Entity;

class ImportOpml
{
    /**
     * @var string
     */
    private $file;

    /**
     * Set file
     *
     * @param string $file
     *
     * @return Login
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}
