<?php
namespace Zeitfaden\MongoDb;


class StationRepository extends AbstractRepository
{

  public function mergeStation($station)
  {
    $this->merge($station);
  }

  public function instantiateStation($document)
  {
    return $this->instantiate($document);
  }
    
 
  public function updateStation($station)
  {
      //fix old bug
      if ($station->getTimestamp() == -1069669904){
          $station->setTimestamp(-5364662400);
      }

      $this->update($station);
  }
   

  public function getStationById($stationId)
  {
    return $this->getById($stationId);
  }

  public function getStationByFileMd5($userId, $fileMd5)
  {
    $dbName = $this->getMongoDbName();
    $collection = $this->getConnection()->$dbName->stations;
    $document = $collection->findOne(array('user_id' => $userId, 'file_md5' => $fileMd5));
    if (!$document)
    {
        throw new \Zeitfaden\Exception\NoMatchException();
    }
    return $this->instantiateStation($document);
  }

  public function getStationByPathToFile($pathToFile)
  {
    $dbName = $this->getMongoDbName();
    $collection = $this->getConnection()->$dbName->stations;
    $document = $collection->findOne(array('path_to_file' => $pathToFile));
    if (!$document)
    {
        throw new \Zeitfaden\Exception\NoMatchException();
    }
    return $this->instantiateStation($document);
  }

   
   
  public function deleteStation($station)
  {
    $this->delete($station);
  }
  
  public function getAllStationsIterator()
  {
    $dbName = $this->getMongoDbName();
    $collection = $this->getConnection()->$dbName->stations;
    $mongoCursor = $collection->find();
    $iterator = new StationsIterator($mongoCursor, $this);
    return $iterator;
  }

  public function getAllStationsInGroupIterator($userId, $groupId)
  {
    $dbName = $this->getMongoDbName();
    $collection = $this->getConnection()->$dbName->stations;
    $mongoCursor = $collection->find(array('group_ids' => $groupId, 'user_id' => $userId));
    $iterator = new StationsIterator($mongoCursor, $this);
    return $iterator;
  }
 
    
   
    
}