<?php

namespace ProVallo\Components\Job;

use Symfony\Component\Console\Output\OutputInterface;

class JobRunner
{
    
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;
    
    public function __construct (OutputInterface $output)
    {
        $this->output = $output;
    }
    
    public function run (array $jobs)
    {
        foreach ($jobs as $job)
        {
            if (!in_array(JobInterface::class, class_implements($job), true))
            {
                $this->output->writeln('The given job is invalid');
                continue;
            }
            
            try
            {
                $this->output->writeln('========== ' . $job->getName() . ' ==========');
                $job->execute($this->output);
            }
            catch (\Exception $ex)
            {
                $this->output->writeln('Job "' . $job->getName() . '" failed to run. See error details below.');
                $this->output->writeln($ex->getMessage());
            }
        }
    }
    
}