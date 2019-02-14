<?php

namespace models;

class ImageResponse
{
    public $status;
    public $message;
    //public $image;

    public function getMessage()
    {
        return $this->message;
    }

    /*
    public function getImage()
    {
        return $this->image;
    }
    */

    public function getStatus()
    {
        return $this->status;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    /*
    public function setImage($image)
    {
        $this->image = $image;
    }
    */

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
