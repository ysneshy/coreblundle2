<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Facet;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\GeneralFacetPreferenceRepository")
 * @ORM\Table(name="claro_general_facet_preference")
 */
class GeneralFacetPreference
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $baseData;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $mail;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $sendMail;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $sendMessage;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="generalFacetPreference"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $role;

    public function getId()
    {
        return $this->id;
    }

    public function setBaseData($boolean)
    {
        $this->baseData = $boolean;
    }

    public function getBaseData()
    {
        return $this->baseData;
    }

    public function setMail($boolean)
    {
        $this->mail = $boolean;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setPhone($boolean)
    {
        $this->phone = $boolean;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setSendMail($boolean)
    {
        $this->sendMail = $boolean;
    }

    public function getSendMail()
    {
        return $this->sendMail;
    }

    public function setSendMessage($boolean)
    {
        $this->sendMessage = $boolean;
    }

    public function getSendMessage()
    {
        return $this->sendMessage;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }
}