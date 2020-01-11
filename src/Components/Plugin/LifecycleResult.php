<?php

namespace ProVallo\Components\Plugin;

use ProVallo\Components\Job\JobInterface;

class LifecycleResult
{
    
    const TYPE_INSTALL   = 'install';
    
    const TYPE_UNINSTALL = 'uninstall';
    
    const TYPE_UPDATE    = 'update';
    
    /**
     * @var string
     */
    protected $type;
    
    /**
     * @var boolean
     */
    protected $success;
    
    /**
     * @var string
     */
    protected $message;
    
    /**
     * @var JobInterface[]
     */
    protected $jobs;
    
    public function __construct (string $type, bool $success, string $message = '')
    {
        $this->type    = $type;
        $this->success = $success;
        $this->message = $message;
        $this->jobs    = [];
    }
    
    public static function create ($result, $type): LifecycleResult
    {
        if ($result instanceof self)
        {
            return $result;
        }
        else if (is_bool($result))
        {
            return new self($type, $result);
        }
        else
        {
            throw new \Exception('Unexpected result type.');
        }
    }
    
    public function isSuccess (): bool
    {
        return $this->success;
    }
    
    public function getMessage (): string
    {
        return $this->message;
    }
    
    public function addJob (JobInterface $job): LifecycleResult
    {
        $this->jobs[] = $job;
        
        return $this;
    }
    
    /**
     * @return JobInterface[]
     */
    public function getJobs (): array
    {
        return $this->jobs;
    }
    
    public function hasJobs (): bool
    {
        return !empty($this->jobs);
    }
    
}