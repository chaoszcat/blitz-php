<?php
class BlitzListener{
    /*
     * will get called every time it polls for job/status
     * returning false will stop the poll
     * @result   the status object from the api
     */
    public  function on_status($result){}
    
    /*
     * will get called whenever an error status occur
     * @error   the error object from the api 
     */
    public  function on_error($error){}
    
    /*
     * will get called when it's completed
     * @result  the result after it's completed
     */
    public  function on_completed($result) {}
    
    /*
     * will get called after it has successfully oborted
     * @result  the result after the abort.
     */
    public  function on_abort($result){}
}