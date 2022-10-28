<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\MandatoryEventInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Event dispatched by the resource controller when a resource deletion is asked.
 */
class DeleteResourceEvent extends Event implements MandatoryEventInterface
{
    private $resource;
    private $files = array();

    /**
     * Constructor.
     *
     * @param AbstractResource $resources
     */
    public function __construct(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource to be deleted.
     *
     * @return AbstractResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set an array of files wich are going to be removed by the kernel.
     *
     * @param array $files
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
