<?php

namespace hcf\handler;

use hcf\handler\kit\KitManager;
use hcf\handler\kit\pay\KitManagerPay;
use hcf\handler\kit\Legendary\KitManagerL;

class HandlerManager {

    public KitManager $kitManager;
    public KitManagerPay $kitManagerPay;
    public KitManagerL $kitManagerL;

    public function __construct(){
        $this->kitManager = new KitManager;
        $this->kitManagerPay = new KitManagerPay;   
        $this->kitManagerL = new KitManagerL;
    }

    public function getKitManager(): KitManager {
        return $this->kitManager;
    }

    public function getKitPayManager(): KitManagerPay {
        return $this->kitManagerPay;
    }

    public function getKitLManager(): KitManagerL {
        return $this->kitManagerL;
    }

}