<?php

class Thread
{
    var $process; // process reference
    var $pipes; // stdio
    var $buffer; // output buffer
    var $output;
    var $error;
    var $timeout;
    var $start_time;

    function __construct()
    {
        $this->process = 0;
        $this->buffer = "";
        $this->pipes = (array)null;
        $this->output = "";
        $this->error = "";
        $this->start_time = time();
        $this->timeout = 0;
    }

    static function create($command)
    {
        $t = new Thread;
        $descriptor = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w"),
        );

        //Open the resource to execute $command
        $t->process = proc_open($command, $descriptor, $t->pipes);

        //Set STDOUT and STDERR to non-blocking
        stream_set_blocking($t->pipes[1], 0);
        stream_set_blocking($t->pipes[2], 0);

        return $t;
    }

    //See if the command is still active
    function isActive()
    {
        $this->buffer .= $this->listen();
        $f = stream_get_meta_data($this->pipes[1]);

        return ! $f["eof"];
    }

    //Close the process
    function close()
    {
        $r = proc_close($this->process);
        $this->process = null;

        return $r;
    }

    //Send a message to the command running
    function tell($thought)
    {
        fwrite($this->pipes[0], $thought);
    }

    //Get the command output produced so far
    function listen()
    {
        $buffer = $this->buffer;
        $this->buffer = "";
        while ($r = fgets($this->pipes[1], 1024)) {
            $buffer .= $r;
            $this->output .= $r;
        }

        return $buffer;
    }

    //Get the status of the current runing process
    function getStatus()
    {
        return proc_get_status($this->process);
    }

    //See if the command is taking too long to run (more than $this->timeout seconds)
    function isBusy()
    {
        return ($this->start_time > 0)
            && ($this->start_time + $this->timeout < time());
    }

    //What command wrote to STDERR
    function getError()
    {
        $buffer = "";
        while ($r = fgets($this->pipes[2], 1024)) {
            $buffer .= $r;
        }

        return $buffer;
    }
}

// Wrapper for Thread class
class Multithread
{
    var $output = [];
    var $error = [];
    var $thread;
    var $commands = array();
    var $hasPool = false;
    var $toExecuted = [];

    function __construct($commands, $sizePool = 0)
    {
        $this->hasPool = $sizePool > 0;
        if ($this->hasPool) {
            $this->toExecuted = array_splice($commands, $sizePool);
        }

        //print_r(count($this->toExecuted));
        $this->commands = $commands;
        foreach ($this->commands as $key => $command) {
            $this->thread[$key] = Thread::create($command);
        }
    }

    function run()
    {
        // Cycle through commands
        while (count($this->commands) > 0) {
            foreach ($this->commands as $key => $command) {
                // Get the output and the errors
                @$this->output[$key] .= $this->thread[$key]->listen();
                @$this->error[$key] .= $this->thread[$key]->getError();

                // Check if command is still active
                if ($this->thread[$key]->isActive()) {
                    $this->output[$key] .= $this->thread[$key]->listen();

                    // Check if command is busy
                    if ($this->thread[$key]->isBusy()) {
                        $this->thread[$key]->close();
                        unset($this->commands[$key]);
                        self::launchNextInQueue();

                    }
                } else {
                    // Close the command and free resources
                    $this->thread[$key]->close();
                    unset($this->commands[$key]);
                    self::launchNextInQueue();
                }
            }
        }

        return $this->output;
    }

    function launchNextInQueue()
    {
        if (count($this->toExecuted) == 0) {
            return true;
        }
        reset($this->toExecuted);
        $keyToExecuted = key($this->toExecuted);
        $this->commands[$keyToExecuted] = $this->toExecuted[$keyToExecuted];
        $this->thread[$keyToExecuted]
            = Thread::create($this->toExecuted[$keyToExecuted]);
        unset($this->toExecuted[$keyToExecuted]);
    }
}