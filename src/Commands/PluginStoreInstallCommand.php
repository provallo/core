<?php

namespace ProVallo\Commands;

use ProVallo\Components\Command;
use ProVallo\Core;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class PluginStoreInstallCommand extends Command
{
    
    /**
     * @var InputInterface
     */
    private $input;
    
    /**
     * @var OutputInterface
     */
    private $output;
    
    protected function configure ()
    {
        $this->setName('plugin:store:install');
        $this->setDescription('Installs a new plugin from the store.');
        
        $this->addArgument('name', InputArgument::REQUIRED, 'The full technical name of the plugin.');
    }
    
    protected function initialize (InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
    }
    
    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $name    = $input->getArgument('name');
        $store   = Core::di()->get('store');
        $results = $store->search($name);
        
        if (empty($results))
        {
            $output->writeln('Plugin by name not found.');
        }
        else if (count($results) > 1)
        {
            /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion('We have found more plugins matching the technical name. Please select the correct one.', array_map(function ($result)
            {
                return $result['label'];
            }, $results));
            
            $selected = $helper->ask($input, $output, $question);
            $result   = array_first(array_filter($results, function ($result) use ($selected)
            {
                return $result['label'] === $selected;
            }));
            
            $this->downloadAndInstall($result);
        }
        else
        {
            $this->downloadAndInstall($results[0]);
        }
    }
    
    private function downloadAndInstall ($plugin)
    {
        $store    = Core::di()->get('store');
        $versions = $store->getAvailableVersions($plugin['label']);
        $count    = count($versions);
        
        if ($count > 1)
        {
            /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion('Which version you wanna install?', array_map(function ($version)
            {
                return $version['version'];
            }, $versions));
            
            $version = $helper->ask($this->input, $this->output, $question);
            $version = array_first(array_filter($versions, function ($result) use ($version)
            {
                return $result['version'] === $version;
            }));
            
            $this->download($plugin, $version);
        }
        else
        {
            $this->download($plugin, $versions[0]);
        }
    }
    
    private function download ($plugin, $version)
    {
        /** @var \ProVallo\Components\Plugin\Manager $plugins */
        $plugins = Core::di()->get('plugins');
        
        $this->output->writeln('Downloading...');
        $filename = sys_get_temp_dir() . '/pv_plugin' . md5(uniqid('pv_plugin', true)) . '.zip';
        
        $contents = file_get_contents($version['filename']);
        file_put_contents($filename, $contents);
        unset ($contents);
        
        $this->output->writeln('Validating...');
        
        $zip = new \ZipArchive();
        $zip->open($filename);
        
        $json = $zip->getFromName('plugin.json');
        $json = json_decode($json, true);
        
        try
        {
            $plugins->get($json['label']);
            
            $this->output->writeln('The plugin is already installed.');
            
            unlink($filename);
            die;
        }
        catch (\Exception $ex)
        {
            // ignore, and continue...
        }
        
        $this->output->writeln('Extracting...');
        
        $destination = Core::path() . 'ext/' . $json['label'];
        
        $zip->extractTo($destination);
        $zip->getFromName('plugin.json');
        $zip->close();
        
        $this->output->writeln('Installing plugin...');
        $plugins->synchronize();
        
        $result = $plugins->install($json['label']);
        
        if ($result->isSuccess())
        {
            $this->output->writeln('The plugin were installed successfully.');
        }
        else
        {
            $this->output->writeln($result->getMessage());
        }
    }
    
}