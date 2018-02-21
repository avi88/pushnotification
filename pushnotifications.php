<?php
    private function droid_sendnotification_message($deviceToken,$messagetitle,$messagedes){		
		$data=array('data'=>array('title'=>$messagetitle,'is_background'=>false,'message'=>$messagedes,'image'=>'','payload'=>array('team'=>'','score'=>''),'timestamp'=>''));
		$fields=array('to'=>$deviceToken,'data'=>$data);	
		if(!defined('FIREBASE_API_KEY')){
        define('FIREBASE_API_KEY','WRITE_YOUR_API_KEY');
        }
        $url = 'https://fcm.googleapis.com/fcm/send'; 
        
		$headers = array(
        'Authorization: key='.FIREBASE_API_KEY,
        'Content-Type: application/json'
        );  		
        $ch = curl_init();       
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));       
        $result = curl_exec($ch);
        if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
	
	
	private function ios_sendnotification_message($deviceToken,$messagetitle,$messagedes){		
		$passphrase = 123456;
		$badge = 1;
		$ctx = stream_context_create();						
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);						
		$fp = stream_socket_client(
			'ssl://gateway.sandbox.push.apple.com:2195', $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if(!$fp)
		exit("Failed to connect: $err $errstr" . PHP_EOL);
		
		$body['aps'] = array(
			'alert' => array(
				'title' => $messagetitle,
				'body' => $messagedes,
			 ),
			'sound' => 'default'
		);						
		$payload = json_encode($body);						
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		$result = fwrite($fp, $msg, strlen($msg));
	}	
	

    //Call functions
	
	$this->db->select("*");		
	$this->db->from("tb_appnotifications");				
	$get = $this->db->get();
	$resultset = $get->result_array();//Get the device id's
	
	$messagedes="Message description";
	foreach($resultset as $forpush){
		if($forpush['devicetype']=='iOS'){					
		$deviceToken = $forpush['gcm_apns_Id'];
		$messagetitle = "Message title";//change this message by your	
		$this->ios_sendnotification_message($deviceToken,$messagetitle,$messagedes);
		}else{					
		$messagetitle = "Message title";//change this message by your
		$deviceToken = $forpush['gcm_apns_Id'];
		$this->droid_sendnotification_message($deviceToken,$messagetitle,$messagedes);
		}
	
	
