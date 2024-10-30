<?php

namespace Pump\User\DbModel;

class UserDbModel
{
    public $address;

    public $nickName;

    public $walletType;

    public $headImgUrl;

    public $content;

    public $created_at;

    public $updated_at;

    public $deleted_at;

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
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

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    /**
     * @param mixed $deleted_at
     */
    public function setDeletedAt($deleted_at): void
    {
        $this->deleted_at = $deleted_at;
    }


}
