<?php

namespace App\Traits;

use App\Entity\Extra\QrCode;

trait QrCodeTrait
{
    /**
     * @var QrCode
     * @ORM\OneToOne(targetEntity=QrCode::class, cascade={"persist", "remove"})
     * @Groups({"default","qrCode"})
     */
    protected $qrCode;

    public function getQrCode()
    {
        return $this->qrCode;
    }

    /**
     * @param mixed $qrCode
     */
    public function setQrCode($qrCode)
    {
        $this->qrCode = $qrCode;

        return $this;
    }
}
