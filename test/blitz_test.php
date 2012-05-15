#!/usr/bin/php
<?php
//Include the test framework 


require_once 'EnhanceTestFramework.php';
require_once '../blitz_api.php';
require_once '../blitz_listener.php';
class Blitz_Listener_Test extends BlitzListener {
    public $max_status = 1;
    public $count_status = 0;
    
    public function on_status($result){
        return $this->count_status++ < $this->max_status;
    }
    public function on_complete($result){
        
    }
    
    public function on_error($result){
        
    }
    
    public function on_abort($result){
        
    }
}
class Blitz_Test extends \Enhance\TestFixture {

    private $blitz_api = null;
    private $mocked_rest_client = null;
    
    public function setUp() {
        $this->blitz_api = new BlitzAPI();
        $this->mocked_rest_client = Enhance\MockFactory::createMock('RestClient');
        $set_expectation = \Enhance\Expect::method('set_option');
        $this->mocked_rest_client->addExpectation($set_expectation);
        $this->blitz_api->rest_client = $this->mocked_rest_client;
    }
    
    public function login_successfully(){
        $result = new stdClass();
        $result->response = '{"ok":true, "api_key":"private-key"}';
        $expected_get = \Enhance\Expect::method('get')->with('login/api');
        $expected_get->times(1);
        $expected_get->returns($result);
        $this->mocked_rest_client->addExpectation($expected_get);
        $this->blitz_api->login();
        \Enhance\Assert::isTrue($this->blitz_api->api_key === 'private-key');
    }
    
    public function login_invalid(){
        $result = new stdClass();
        $result->response = '{"error":"login", "reason":"test"}';
        $expected_get = \Enhance\Expect::method('get')->with('login/api');
        $expected_get->times(1);
        $expected_get->returns($result);
        $this->mocked_rest_client->addExpectation($expected_get);
        \Enhance\Assert::throws($this->blitz_api, 'login');
    }
    
    public function parses_successfully(){
        $valid_command = '-r california -p 1-1000:60 http://test.blitz.io';
        $valid_result = new stdClass();
        $valid_result->response = '{"ok":true, "command":{"steps":[{"url":"http://test.blitz.io"}], "region":"california", "pattern":{"iterations":1, "intervals":[{"iterations":1, "start":1, "end":1000, "duration":60}]}}}';
        $expected_post = \Enhance\Expect::method('post')->with('api/1/parse', array('command'=>$valid_command));
        $expected_post->times(1);
        $expected_post->returns($valid_result);
        $this->mocked_rest_client->addExpectation($expected_post);
        $result = $this->blitz_api->parse($valid_command);
        \Enhance\Assert::isObject($result);
        \Enhance\Assert::isTrue(isset($result->steps));
        \Enhance\Assert::isTrue(isset($result->region));
        \Enhance\Assert::isTrue(isset($result->pattern));
        \Enhance\Assert::isTrue(isset($result->pattern->intervals));
    }

    public function parses_unsuccessfully(){
        $valid_command = '-fake california -p 1-1000:60 http://test.blitz.io';
        $valid_result = new stdClass();
        $valid_result->response = '{"error":"syntax", "reason":"Unknown option -fake"}';
        $expected_post = \Enhance\Expect::method('post')->with('api/1/parse', array('command'=>$valid_command));
        $expected_post->times(1);
        $expected_post->returns($valid_result);
        $this->mocked_rest_client->addExpectation($expected_post);
        \Enhance\Assert::throws($this->blitz_api, 'parse', array($valid_command));
    }
    public function rush_successfully(){
        $valid_result_status = new stdClass();
        $valid_result_status->response = '{"status":"running"}';
        
        $valid_result_post = new stdClass();
        $valid_result_post->response = '{"ok":true, "job_id":"jobid123", "status":"temp", "region":"california"}';
        
        $expected_get_status = \Enhance\Expect::method('get')->with('api/1/jobs/jobid123/status');
        $expected_get_status->times(2);
        $expected_get_status->returns($valid_result_status);
        
        $expected_post_execute = \Enhance\Expect::method('post')->with('api/1/curl/execute', array());
        $expected_post_execute->returns($valid_result_post);
        
        $this->mocked_rest_client->addExpectation($expected_get_status);
        $this->mocked_rest_client->addExpectation($expected_post_execute);
        
        
        $listener->max_status = 2;
        $listener->count = 0;
        $this->blitz_api->listener = new Blitz_Listener_Test();
        $this->blitz_api->rush(array());
    }
    
    
    public function sprint_successfully(){
        $valid_result_status = new stdClass();
        $valid_result_status->response = '{"status":"running"}';
        
        $valid_result_post = new stdClass();
        $valid_result_post->response = '{"ok":true, "job_id":"jobid123", "status":"temp", "region":"california"}';
        
        $expected_get_status = \Enhance\Expect::method('get')->with('api/1/jobs/jobid123/status');
        $expected_get_status->times(2);
        $expected_get_status->returns($valid_result_status);
        
        $expected_post_execute = \Enhance\Expect::method('post')->with('api/1/curl/execute', array());
        $expected_post_execute->returns($valid_result_post);
        
        $this->mocked_rest_client->addExpectation($expected_get_status);
        $this->mocked_rest_client->addExpectation($expected_post_execute);
        
        
        $listener->max_status = 2;
        $listener->count = 0;
        $this->blitz_api->listener = new Blitz_Listener_Test();
        $this->blitz_api->rush(array());
    }
    
    
    public function abort_successfully(){
        $valid_result_status = new stdClass();
        $valid_result_status->response = '{"ok":true}';
        
        $expected_put_status = \Enhance\Expect::method('put')->with('api/1/jobs/jobid123/abort', null);
        $expected_put_status->times(1);
        $expected_put_status->returns($valid_result_status);
        $this->mocked_rest_client->addExpectation($expected_put_status);
        
        $this->blitz_api->listener = new Blitz_Listener_Test();
        $this->blitz_api->abort('jobid123');
    }
}
\Enhance\Core::runTests();