<?php

/*
 * Copyright (c) 2015 Anuj Nair
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpJsonMarshallerTests\ExampleClass;

use PhpJsonMarshaller\Annotations\MarshallConfig;
use PhpJsonMarshaller\Annotations\MarshallProperty;

/**
 * A class which has one of every iType, and where everything is set through setters
 * @MarshallConfig(ignoreUnknown=true)
 */
class User
{

    /**
     * @var int $id
     * @MarshallProperty(name="id", type="int")
     */
    protected $id;

    /**
     * @var string $firstName
     * @MarshallProperty(name="firstName", type="string")
     */
    protected $firstName;

    /**
     * @var bool $active
     * @MarshallProperty(name="active", type="boolean")
     */
    protected $active;

    /**
     * @var \DateTime $firstLogin
     * @MarshallProperty(name="firstLogin", type="\DateTime")
     */
    protected $firstLogin;

    /**
     * @var float $rank
     * @MarshallProperty(name="rank", type="float")
     */
    protected $rank;

    /**
     * @var Address
     * @MarshallProperty(name="homeAddress", type="\PhpJsonMarshallerTests\ExampleClass\Address")
     */
    protected $homeAddress;

    /**
     * @var Address
     * @MarshallProperty(name="workAddress", type="\PhpJsonMarshallerTests\ExampleClass\Address")
     */
    protected $workAddress;

    /**
     * @var Flag[]
     * @MarshallProperty(name="flags", type="\PhpJsonMarshallerTests\ExampleClass\Flag[]")
     */
    protected $flags;

    /**
     * @var \DateTime[]
     * @MarshallProperty(name="loginDates", type="\DateTime[]")
     */
    protected $loginDates;


    /**
     * Empty constructor to show we can have one
     */
    public function __construct()
    {
    }

    /**
     * @return int
     * @MarshallProperty(name="id", type="int")
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @MarshallProperty(name="id", type="int")
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     * @MarshallProperty(name="firstName", type="string")
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @MarshallProperty(name="firstName", type="string")
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return boolean
     * @MarshallProperty(name="active", type="bool")
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @MarshallProperty(name="active", type="bool")
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return \DateTime
     * @MarshallProperty(name="firstLogin", type="\DateTime")
     */
    public function getFirstLogin()
    {
        return $this->firstLogin;
    }

    /**
     * @param \DateTime $firstLogin
     * @MarshallProperty(name="firstLogin", type="\DateTime")
     */
    public function setFirstLogin($firstLogin)
    {
        $this->firstLogin = $firstLogin;
    }

    /**
     * @return float
     * @MarshallProperty(name="rank", type="float")
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param float $rank
     * @MarshallProperty(name="rank", type="float")
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }

    /**
     * @return Address
     * @MarshallProperty(name="homeAddress", type="\PhpJsonMarshallerTests\ExampleClass\Address")
     */
    public function getHomeAddress()
    {
        return $this->homeAddress;
    }

    /**
     * @param Address $address
     * @MarshallProperty(name="homeAddress", type="\PhpJsonMarshallerTests\ExampleClass\Address")
     */
    public function setHomeAddress($address)
    {
        $this->homeAddress = $address;
    }

    /**
     * @return Address
     * @MarshallProperty(name="workAddress", type="\PhpJsonMarshallerTests\ExampleClass\Address")
     */
    public function getWorkAddress()
    {
        return $this->workAddress;
    }

    /**
     * @param Address $address
     * @MarshallProperty(name="workAddress", type="\PhpJsonMarshallerTests\ExampleClass\Address")
     */
    public function setWorkAddress($address)
    {
        $this->workAddress = $address;
    }

    /**
     * @return Flag[]
     * @MarshallProperty(name="flags", type="\PhpJsonMarshallerTests\ExampleClass\Flag[]")
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param Flag[] $flags
     * @MarshallProperty(name="flags", type="\PhpJsonMarshallerTests\ExampleClass\Flag[]")
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * @return \DateTime[]
     * @MarshallProperty(name="loginDates", type="\DateTime[]")
     */
    public function getLoginDates()
    {
        return $this->loginDates;
    }

    /**
     * @param \DateTime[] $loginDates
     * @MarshallProperty(name="loginDates", type="\DateTime[]")
     */
    public function setLoginDates($loginDates)
    {
        $this->loginDates = $loginDates;
    }

}
