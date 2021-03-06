<?php

namespace ProVallo\Components;

/**
 * Class RESTController
 *
 * This controller is used to be extended to simplify creating controllers
 * which creates, updates and removes models. It also supports getting one or
 * all items from a model.
 *
 * @package CMS\Components
 */
abstract class RESTController extends Controller
{
    
    /**
     * @var string|\Favez\Mvc\ORM\Entity
     */
    protected $className = '';
    
    public function __construct ()
    {
        parent::__construct();
    }
    
    protected function getRepository ()
    {
        return $this->models()->getRepository($this->className);
    }
    
    /**
     * This action fetched a model by id and returns a json response.
     *
     * @return string
     */
    public function getAction ()
    {
        $id    = (int) $this->request()->getParam('id');
        $model = $this->getRepository()->find($id);
        
        if ($model instanceof $this->className)
        {
            return $this->json()->success([
                'data' => $model->toArray()
            ]);
        }
        
        return $this->json()->failure();
    }
    
    /**
     * This action fetches all models and returns a json response.
     *
     * @return string
     * @throws \Exception
     */
    public function listAction ()
    {
        return $this->json()->success([
            'data' => $this->models()->getRepository($this->className)->findAll()->toArray()
        ]);
    }
    
    /**
     * This action creates/updates an existing model and returns a json
     * response.
     *
     * @return string
     */
    public function saveAction ()
    {
        $id   = (int) $this->request()->getParam('id');
        $data = $this->request()->getParams();
        $item = $this->getRepository()->find($id);
        
        if (!($item instanceof $this->className))
        {
            $item = $this->className::create();
            
            $this->onCreate($item, $data);
        }
        
        $this->beforeSave($item, $data);
        
        if (method_exists($item, 'validate'))
        {
            $result = $item->validate();
        }
        else
        {
            $result = true;
        }
        
        if (isSuccess($result))
        {
            $this->getRepository()->save($item);
            $this->afterSave($item, $data);
            
            return $this->json()->success($item->toArray());
        }
        
        return $this->json()->failure($result);
    }
    
    /**
     * This action removes a model and returns a json response.
     * Using the described events it is possible to prevent the deletion
     * process.
     *
     * @return string
     */
    public function removeAction ()
    {
        $id   = (int) $this->request()->getParam('id');
        $item = $this->getRepository()->find($id);
        
        if ($item instanceof $this->className)
        {
            $result = $this->beforeRemove($item);
            
            if (empty($result) || isSuccess($result))
            {
                $this->getRepository()->remove($item);
                $this->afterRemove();
                
                return $this->json()->success();
            }
            
            return $this->json()->failure($result);
        }
        
        return $this->json()->failure();
    }
    
    /**
     * Event that will be called before a model is removed.
     *
     * @param $item
     */
    protected function beforeRemove ($item)
    {
    }
    
    /**
     * Event that will be called after a model was removed.
     */
    protected function afterRemove ()
    {
    }
    
    /**
     * Event that will be called if a new instance of a model were created.
     *
     * @param $item
     * @param $data
     */
    protected function onCreate ($item, $data)
    {
    }
    
    /**
     * Event that will be called before a model will be saved.
     *
     * @param $item
     * @param $data
     */
    protected function beforeSave ($item, $data)
    {
    }
    
    /**
     * Event that will be called after a model was saved.
     *
     * @param $item
     * @param $data
     */
    protected function afterSave ($item, $data)
    {
    }
    
}