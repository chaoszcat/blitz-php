<?php
require_once 'rest_client.php';

class BlitzAPI {

    
    public $api_key = null;
    public $rest_client = null;
    public $email = null;
    public $listener = null;
    
    public static $WAIT = 2;

    public function __construct($email = false, $api_key = false, $host = 'blitz.io', $port = 80) {
        //this means it's authenticated somewhere else, through cli
        if ($email === false || $api_key === false){
            return;
        }
        
        $this->email = $email;
        $this->api_key = $api_key;
        $this->get_rest_client($host, $port); //create the first time and cache
        $this->login();
    }
    
    /*
     * first request uses the api key supplied by user, since then
     * it is set to $this->api_key and host and port already create since
     * I reuse the same client
     */

    private function get($url) {
        sleep(self::$WAIT);
        $rest_client = $this->get_rest_client();
        $result = $rest_client->get($url);
        return json_decode($result->response);
    }

    private function post($url, $parameters) {
        sleep(self::$WAIT);
        $rest_client = $this->get_rest_client();
        $result = $rest_client->post($url, $parameters);
        return json_decode($result->response);
    }
    
    private function put($url, $parameters = null){
        sleep(self::$WAIT);
        $rest_client = $this->get_rest_client();
        $result = $rest_client->put($url, $parameters);
        return json_decode($result->response);        
    }
    public function login(){
        
        $result = $this->get('login/api');
        if (isset($result->error)) {
            throw new BlitzException('Invalid login');
        } else {
            $this->api_key = $result->api_key;
        }
    }
    public function get_rest_client($host = 'blitz.io', $port = 80) {
        if ($this->rest_client === null) {

            $scheme = 'http';
            $this->rest_client = new \RestClient(array(
                        'base_url' => $scheme . '://' . $host . ':' . $port
                            )
            );
        }
        $headers = array(
            'X-API-User' => $this->email,
            'X-API-Key' => $this->api_key,
            'X-API-Client' => 'php',
            'Content-Type' => 'application/json'
        );
        $this->rest_client->set_option('headers', $headers);
        return $this->rest_client;
    }

    public function parse($command) {
        $result = $this->post('api/1/parse', array('command' => $command));
        
        if (isset($result->error)) {
            throw new BlitzException($result->reason);
        } else {
            
            return $result->command;
        }
    }

    public function curl($command) {
        if ($this->listener === null) 
            throw new BlitzException('Please provide listeners');
        $command = $this->parse($command);
        if (isset($command->pattern)) {
            $this->rush($command);
        } else {
            $this->sprint($command);
        }
    }

    public function rush($command) {
        $result = $this->post('api/1/curl/execute', $command);
        //acquire the job status
       
        if ($result->ok) {//good, now we got the job id
            $job_id = $result->job_id;
            $status = $result->status;
            $region = $result->region;
            $this->poll($job_id);
        }
        else{
            throw new BlitzException('Unable to execute rush');
        }
    }

    public function sprint($command) {
        $result = $this->post('api/1/curl/execute', $command);
        //acquire the job status
        if ($result->ok) {//good, now we got the job id
            $job_id = $result->job_id;
            $status = $result->status;
            $region = $result->region;
            $this->poll($job_id);
        }
        else{
            throw new BlitzException('Unable to execute sprint');
        }
    }
    public function abort($job_id){
        $result = $this->put('api/1/jobs/'.$job_id.'/abort');

        if ($result->ok){
            $this->listener->on_abort($result);
        }
        else{
            throw new BlitzException('Unable to abort job id: '.$job_id);
        }
    }
    private function poll($job_id) {

        //now we poll the job
        while (true) {
            $result = $this->get('api/1/jobs/' . $job_id . '/status');
            switch ($result->status) {
                case 'queued':
                case 'running':
                    if ($this->listener->on_status($result)){
                        break;
                    }
                    else{
                        break 2;
                    }
                    
                case 'completed':
                    $this->listener->on_completed($result->result);
                    break 2;
                case 'error':
                    $this->listener->on_error($result);
                    break 2;
            }
        }
    }


}

class BlitzException extends Exception {
    
}