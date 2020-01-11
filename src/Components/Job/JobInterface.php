<?php

namespace ProVallo\Components\Job;

use Symfony\Component\Console\Output\OutputInterface;

interface JobInterface
{
    
    /**
     * Returns the job name.
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Executes the job defined logic.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function execute (OutputInterface $output): int;
    
}