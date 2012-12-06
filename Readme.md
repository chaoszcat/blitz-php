![Blitz logo](https://a248.e.akamai.net/camo.github.com/876df0c07bae85f7a2d805c9264742857159deff/687474703a2f2f626c69747a2e696f2f696d616765732f6c6f676f322e706e67)

## Make load and performance a fun sport. ##


* Run a sprint from around the world
* Rush your API and website to scale it out
* Condition your site around the clock


## Getting started ##


Login to blitz.io and in the blitz bar type the following to acquire your API key:
    --api-key

Include the following PHP files to get started: blitz_api.php and blitz_listener.php
Now, you are set to listen to events to get the data from the API by extending BlitzListener


## Example ##

    require_once 'blitz_api.php';

    class MyBlitzListener extends BlitzListener {

        //will provide the result on the test completed
        public function on_complete($result){
            echo var_dump($result);
        }

        //will provide status data as it polls the API
        public function on_status($result){
            echo var_dump($result);
        }
    }

    //acquire the username and api key from your blitz settings
    $blitz_api = new BlitzApi(<username>, <api key>);

    $blitz_api->listener = new MyBlitzListener();//extends from blitz_listener
    $blitz_api->curl('-r california -p 1-1000:60 http://test.blitz.io');