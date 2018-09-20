<?php

namespace models;

class ImageResponse
{
    private $status;
    private $data;

    public function getMessage()
    {
        return $this->message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setMessage($message)
    {
    	$this->message = $message;
    }

    public function setStatus($status)
    {
    	$this->status = $status;
    }
}
