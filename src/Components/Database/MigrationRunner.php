<?php

namespace ProVallo\Components\Database;

use DirectoryIterator;
use Exception;
use IteratorIterator;
use ProVallo\Core;
use ProVallo\Models\Schema\Schema;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationRunner
{
    
    /**
     * The directory where the migrations sit.
     *
     * @var string
     */
    protected $directory;
    
    /**
     * The optional pluginID the migrations are for. If the migrations are for
     * the cms itself, it contains -1
     *
     * @var integer
     */
    protected $pluginID;
    
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;
    
    /**
     * The namespace for the className
     *
     * @var string
     */
    protected $namespace;
    
    public function __construct ($pluginID, $directory)
    {
        $this->pluginID  = $pluginID;
        $this->directory = $directory;
        $this->output    = new BufferedOutput();
    }
    
    public function setOutput (OutputInterface $output)
    {
        $this->output = $output;
    }
    
    public function setNamespace ($namespace)
    {
        $this->namespace = $namespace;
    }
    
    public function run ()
    {
        try
        {
            $migrations = $this->findMigrations();
        }
        catch (Exception $ex)
        {
            return [
                'success'   => false,
                'exception' => $ex
            ];
        }
        
        $executedCount = 0;
        
        foreach ($migrations as $version => $statements)
        {
            if ($this->pluginID === -1 && $version === 100 && !$this->tableExists('schema_version'))
            {
                $this->output->writeln('Running ' . $version);
                $this->executeSQL($statements);
                
                $executedCount++;
    
                $schema            = Schema::create();
                $schema->version   = $version;
                $schema->startDate = date('Y-m-d H:i:s');
                $schema->endDate   = date('Y-m-d H:i:s');
                $schema->error     = null;
                $schema->pluginID  = $this->pluginID;
                $schema->save();
                
                continue;
            }
            
            $schema = Schema::repository()->findOneBy([
                'version'  => $version,
                'pluginID' => $this->pluginID
            ]);
            
            if (!($schema instanceof Schema))
            {
                $schema            = Schema::create();
                $schema->version   = $version;
                $schema->startDate = null;
                $schema->endDate   = null;
                $schema->error     = null;
                $schema->pluginID  = $this->pluginID;
            }
            
            if ($schema->endDate !== null)
            {
                continue;
            }
            
            $this->output->writeln('Running ' . $version);
            
            $schema->startDate = date('Y-m-d H:i:s');
            
            try
            {
                $this->executeSQL($statements);
                
                $schema->endDate = date('Y-m-d H:i:s');
                $schema->save();
                
                $executedCount++;
            }
            catch (Exception $ex)
            {
                $schema->endDate = null;
                $schema->error   = json_encode([
                    'message' => $ex->getMessage(),
                    'class'   => get_class($ex)
                ]);
                
                $schema->save();
                
                return [
                    'success'   => false,
                    'exception' => $ex
                ];
            }
        }
        
        return [
            'success' => true,
            'total'   => $executedCount
        ];
    }
    
    protected function tableExists ($table)
    {
        try
        {
            Core::db()->query('SHOW CREATE TABLE ' . $table)->execute();
        }
        catch (Exception $ex)
        {
            return false;
        }
        
        return true;
    }
    
    protected function executeSQL ($statements)
    {
        foreach ($statements as $statement)
        {
            $sql    = $statement[0];
            $params = $statement[1];
        
            Core::db()->query($sql)->execute($params);
        }
    }
    
    protected function findMigrations ()
    {
        $migrations = [];
        $iterator   = new IteratorIterator(new DirectoryIterator($this->directory));
        
        foreach ($iterator as $file)
        {
            if ($file->isDot() || $file->getFilename() === '.gitkeep')
            {
                continue;
            }
            
            $filename = path($this->directory, $file->getFilename());
            $version  = (int) $file->getFilename();
            
            require_once $filename;
            
            $className = $this->namespace . 'Migration_' . $version;
            
            if (class_exists($className) === false)
            {
                throw new Exception('Invalid migration file: ' . $file->getFilename());
            }
            
            /** @var \ProVallo\Components\Database\Migration $migration */
            $migration = new $className(Core::instance(), $version);
            $migration->up();
            
            $migrations[$version] = $migration->getSQL();
        }
        
        return $migrations;
    }
    
}