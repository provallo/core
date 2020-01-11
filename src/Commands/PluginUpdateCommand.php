<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Components\Job\JobRunner;
use ProVallo\Components\Plugin\Bootstrap;
use ProVallo\Components\Plugin\Manager;
use ProVallo\Components\Plugin\Updater;
use ProVallo\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class PluginUpdateCommand extends Command
{
    
    public function configure ()
    {
        $this->setName('plugin:update')
            ->setDescription('Updates a single plugin.');
        
        $this->addArgument('name', InputArgument::REQUIRED, 'The plugin name');
    }
    
    public function execute (InputInterface $input, OutputInterface $output)
    {
        $name   = $input->getArgument('name');
        $result = Core::plugins()->update($name);
        
        if ($result->isSuccess())
        {
            $output->writeln('The plugin were updated successfully.');
            
            if ($result->hasJobs())
            {
                $output->writeln('Running post jobs ...');
                
                $runner = new JobRunner($output);
                $runner->run($result->getJobs());
            }
        }
        else
        {
            if ($result['code'] === 304)
            {
                /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
                $helper   = $this->getHelper('question');
                $question = new ConfirmationQuestion('The plugin is already up-to-date. Do you want to check for updates from the store? [yes]: ');
                $result   = $helper->ask($input, $output, $question);
                
                if ($result === true)
                {
                    $this->checkForUpdates($input, $output, $name);
                }
            }
            else
            {
                $output->writeln($result->getMessage());
            }
        }
    }
    
    protected function checkForUpdates (InputInterface $input, OutputInterface $output, $name)
    {
        $plugins = Core::plugins();
        $updater = $plugins->getUpdater();
        
        $plugin = $plugins->loadInstance($name);
        $update = $updater->checkForUpdate($plugin);
        
        if (!($update instanceof Updater\Update))
        {
            $output->writeln('No updates available.');
            
            return;
        }
        
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('A new version (' . $update->getVersion() . ') is available! Install? [yes]: ');
        $result   = $helper->ask($input, $output, $question);
        
        if ($result === true)
        {
            $filename = $update->download();
            
            if ($update->extract($filename))
            {
                $plugins->resetInstance($name);
                
                $result = $plugins->update($name);
                
                if ($result->isSuccess())
                {
                    $output->writeln('The update were installed successfully.');
                    
                    if ($result->hasJobs())
                    {
                        $output->writeln('Running post jobs ...');
                        
                        $runner = new JobRunner($output);
                        $runner->run($result->getJobs());
                    }
                }
                else
                {
                    $output->writeln($result->getMessage());
                }
            }
            else
            {
                $output->writeln('Unable to unzip the plugin file...');
                $output->writeln('Filename: ' . $filename);
            }
        }
    }
    
}