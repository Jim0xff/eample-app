<?php

namespace Pump\User\Dto;

class UserDTO
{
    public $address;

    public $nickName;

    public $walletType;

    public $headImgUrl;

    public $website;

    public $twitterLink;

    public $telegramLink;

    public $followingCnt;

    public $followedCnt;

    public $likeCnt;

    public  $content;

    public $created_at;

    public $updated_at;

    public $followed = false;

    /**
     * @return mixed
     */
    public function getLikeCnt()
    {
        return $this->likeCnt;
    }

    /**
     * @param mixed $likeCnt
     */
    public function setLikeCnt($likeCnt): void
    {
        $this->likeCnt = $likeCnt;
    }



    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getNickName()
    {
        return $this->nickName;
    }

    /**
     * @param mixed $nickName
     */
    public function setNickName($nickName): void
    {
        $this->nickName = $nickName;
    }

    /**
     * @return mixed
     */
    public function getWalletType()
    {
        return $this->walletType;
    }

    /**
     * @param mixed $walletType
     */
    public function setWalletType($walletType): void
    {
        $this->walletType = $walletType;
    }

    /**
     * @return mixed
     */
    public function getHeadImgUrl()
    {
        return $this->headImgUrl;
    }

    /**
     * @param mixed $headImgUrl
     */
    public function setHeadImgUrl($headImgUrl): void
    {
        $this->headImgUrl = $headImgUrl;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     */
    public function setWebsite($website): void
    {
        $this->website = $website;
    }

    /**
     * @return mixed
     */
    public function getTwitterLink()
    {
        return $this->twitterLink;
    }

    /**
     * @param mixed $twitterLink
     */
    public function setTwitterLink($twitterLink): void
    {
        $this->twitterLink = $twitterLink;
    }

    /**
     * @return mixed
     */
    public function getTelegramLink()
    {
        return $this->telegramLink;
    }

    /**
     * @param mixed $telegramLink
     */
    public function setTelegramLink($telegramLink): void
    {
        $this->telegramLink = $telegramLink;
    }

    /**
     * @return mixed
     */
    public function getFollowingCnt()
    {
        return $this->followingCnt;
    }

    /**
     * @param mixed $followingCnt
     */
    public function setFollowingCnt($followingCnt): void
    {
        $this->followingCnt = $followingCnt;
    }

    /**
     * @return mixed
     */
    public function getFollowedCnt()
    {
        return $this->followedCnt;
    }

    /**
     * @param mixed $followedCnt
     */
    public function setFollowedCnt($followedCnt): void
    {
        $this->followedCnt = $followedCnt;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     */
    public function setUpdatedAt($updated_at): void
    {
        $this->updated_at = $updated_at;
    }


}
