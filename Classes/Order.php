<?php
	namespace DELIVERY\Order;
	class Order {
	    private $ID;
	    private $Client_ID;
	    private $Status;
	    // Constructor
	    public function __construct($ID, $Client_ID){
	        $this->ID = $ID;
	        $this->Client_ID = $Client_ID;
	        $this->Status = 'Pending';
	    }
}