<?php
class BlitzListener{
    public  function on_status($result){}//returning false will stop the poll
    public  function on_error($error){}
    public  function on_completed($result) {}
    public  function on_abort($result){}
}