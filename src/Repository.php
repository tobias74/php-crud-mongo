<?php
namespace PhpCrudMongo;


class Repository
{
    protected $connection = false;
    protected $mapper;
    
    public function __construct($config, $mapper)
    {
        $this->config = $config;
        $this->mapper = $mapper;
    }

    protected function getEntityWords($words, $offset)
    {
        $commandWords = array_slice($words,$offset);
        $entityWords = array();
        $lastWord="";
        foreach ($commandWords as $word){
            if (($word === "And") || ($word === "Or")) {
                $entityWords[] = $lastWord;
                $entityWords[] = $word;
                $lastWord = "";
            }
            else {
                $lastWord.=$word;
            }
        }
        $entityWords[]=$lastWord;
        return $entityWords;   
    }

    protected function makeSpecification($entityWords, $arguments)
    {
        if (count($entityWords) === 1)
        {
            $criteriaMaker = new \PhpVisitableSpecification\CriteriaMaker();
            $criteria = $criteriaMaker->equals(lcfirst($entityWords[0]), $arguments[0]);
            return $criteria;
        }
        else if ((count($entityWords) == 3) && ($entityWords[1] === "And"))
        {
            $criteriaMaker = new \PhpVisitableSpecification\CriteriaMaker();
            $criteriaA = $criteriaMaker->equals(lcfirst($entityWords[0]), $arguments[0]);
            $criteriaB = $criteriaMaker->equals(lcfirst($entityWords[2]), $arguments[1]);
            $criteria =  $criteriaA->logicalAnd( $criteriaB );
            return $criteria;
        }
        else if ((count($entityWords) == 3) && ($entityWords[1] === "Or"))
        {
            $criteriaMaker = new \PhpVisitableSpecification\CriteriaMaker();
            $criteriaA = $criteriaMaker->equals(lcfirst($entityWords[0]), $arguments[0]);
            $criteriaB = $criteriaMaker->equals(lcfirst($entityWords[2]), $arguments[1]);
            $criteria =  $criteriaA->logicalOr( $criteriaB );
            return $criteria;
        }
        else 
        {
            throw new \ErrorException("Wrong Entitywords? ".print_r($entityWords, true));
        }
        
    }

    public function __call($name, $arguments)
    {
        $words = $this->splitByCamelCase($name);

        if (($words[0] === "get") && ($words[1] === "By")){
            $entityWords = $this->getEntityWords($words,2);
            $criteria = $this->makeSpecification($entityWords, $arguments);
            return $this->getBySpecification($criteria);
        }
        else if (($words[0] === "get") && ($words[1] === "One") && ($words[2] === "By")){
            $entityWords = $this->getEntityWords($words,3);
            $criteria = $this->makeSpecification($entityWords, $arguments);
            return $this->getOneBySpecification($criteria);
        }
        else 
        {
            throw new \ErrorException('Method not found '.$name);
        }
    }


    protected function splitByCamelCase($camelCaseString) 
    {
        $re = '/(?<=[a-z])(?=[A-Z])/x';
        $a = preg_split($re, $camelCaseString);
        return $a;
    }

    protected function getConfig()
    {
        return $this->config;
    }
    
    protected function getMapper()
    {
        return $this->mapper;
    }

    protected function getUniqueId()
    {
        $uid=uniqid();
        $uid.=rand(100000,999999);
        return $uid;
    }
    
    protected function getConnection()
    {
        if (!$this->connection)
        {
            $this->connection = new \MongoDB\Client("mongodb://".$this->getConfig()->mongoDbHost.":27017");        
        }
        
        return $this->connection;

    }
    
    public function getMongoDbName()
    {
        return $this->getConfig()->mongoDbName;
    }
    
    protected function instantiate($document)
    {
        return $this->getMapper()->instantiate($document);
    }
    
    protected function mapToDocument($entity)
    {
        return $this->getMapper()->mapToDocument($entity);
    }
    
    public function merge($entity)
    {
        if ($entity->getId() == false)
        {
          $entity->setId("s".$this->getUniqueId());
        }
    
        $this->update($entity);
    }
    
    public function update($entity)
    {
        $document = $this->mapToDocument($entity);
        
        $dbName = $this->getMongoDbName();
        $collectionName = $this->getMapper()->getCollectionName();
        $collection = $this->getConnection()->$dbName->$collectionName;
        $collection->updateOne(array('id' => $entity->getId()), array('$set' => $document), array("upsert" => true));        
    }
    
    public function delete($entity)
    {
        $dbName = $this->getMongoDbName();
        $collectionName = $this->getMapper()->getCollectionName();
        $collection = $this->getConnection()->$dbName->$collectionName;
        $collection->deleteOne(array('id' => $entity->getId()));        
    }
    
    public function getById($entityId)
    {
        return $this->getOneByIdAndId($entityId, $entityId);
        
        $criteriaMaker = new \PhpVisitableSpecification\CriteriaMaker();
        $criteria = $criteriaMaker->hasId($entityId);
        return $this->getOneBySpecification($criteria);
    }
    
    public function getAll()
    {
        $dbName = $this->getMongoDbName();
        $collectionName = $this->getMapper()->getCollectionName();
        $collection = $this->getConnection()->$dbName->$collectionName;
        $mongoCursor = $collection->find();
        $iterator = new EntityIterator($mongoCursor, $this->getMapper());
        return $iterator;
    }
    
    
    public function getOneBySpecification($criteria)
    {
        $dbName = $this->getMongoDbName();
        $collectionName = $this->getMapper()->getCollectionName();
        $collection = $this->getConnection()->$dbName->$collectionName;
        $document = $collection->findOne($this->getWhereArray($criteria));
        if (!$document)
        {
            throw new NoMatchException("not found in facade here...");
        }
        return $this->instantiate($document);
    }    
 
    public function getBySpecification($criteria)
    {
        $dbName = $this->getMongoDbName();
        $collectionName = $this->getMapper()->getCollectionName();
        $collection = $this->getConnection()->$dbName->$collectionName;
        $mongoCursor = $collection->find($this->getWhereArray($criteria));
        $iterator = new EntityIterator($mongoCursor, $this->getMapper());
        return $iterator;
    }    
 
    protected function getWhereArray($criteria)
    {
      $whereArrayMaker = new MongoWhereArray($this->getMapper());
      $criteria->acceptVisitor($whereArrayMaker);
      $whereArray = $whereArrayMaker->getArrayForCriteria($criteria);
    
      //error_log(json_encode($whereArray));

      return $whereArray;
    }    
}