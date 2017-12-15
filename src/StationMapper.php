<?php
namespace Zeitfaden\MongoDb;


class StationMapper
{
    
    public function getCollectionName()
    {
        return "stations";
    }
    
    protected function produceStation()
    {
        return new \Zeitfaden\Model\ZeitStation();
    }
    
    protected function getMap()
    {
        return array(
            'id' => 'id',
            'userId' => 'user_id',
            'pathToFile' => 'path_to_file',
            'fileMd5' => 'file_md5'
        );
    }
    
    public function getColumnForField($fieldName)
    {
        $map = $this->getMap();
        return $map[lcfirst($fieldName)];
    }
    
    public function instantiate($document)
    {
        $resultHash = json_decode(\MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP($document)),true);
        $station = $this->produceStation();
    
    
        $station->setId( $resultHash['id']);
        $station->setUserId( $resultHash['user_id']);
        $station->setTitle( isset($resultHash['title']) ? $resultHash['title'] : '');
        $station->setDescription( $resultHash['description']);
        $station->setPublishStatus( $resultHash['publish_status']);
    
        $station->setLatitude( isset($resultHash['location']['coordinates'][1]) ? $resultHash['location']['coordinates'][1] : null);
        $station->setLongitude( isset($resultHash['location']['coordinates'][0]) ? $resultHash['location']['coordinates'][0] :  null);
    
        $station->setTimezone( $resultHash['timezone']);
        $station->setTimestamp( $resultHash['timestamp']);
        $station->setStorageId( $resultHash['storage_id']);
        $station->setModifiedAtTimestamp( $resultHash['modified_at']);
        $station->setCreatedAtTimestamp( $resultHash['created_at']);
    
        $station->setGroupIds( isset($resultHash['group_ids']) ? $resultHash['group_ids'] : array());
        $station->setReadPermissions( isset($resultHash['read_permissions']) ? $resultHash['read_permissions'] : array());
        $station->setWritePermissions( isset($resultHash['write_permissions']) ? $resultHash['write_permissions'] : array());
    
        if ( isset($resultHash['path_to_file']) && ($resultHash['path_to_file'] !== '') )
        {
          $station->setFileMd5( $resultHash['file_md5']);
          $station->setFileSize( $resultHash['file_size']);
          $station->setFileType( $resultHash['file_type']);
          $station->setPathToFile( $resultHash['path_to_file']);
          $station->setFileName( $resultHash['file_name']);
        }
    
        return $station;
    }
    
    public function mapToDocument($station)
    {
         $document = array(
          'id' => $station->getId(),
          'user_id' => $station->getUserId(),
          'title' => $station->getTitle(),
          'description' => $station->getDescription(),
          'publish_status' => $station->getPublishStatus(),

          'timestamp' => intval($station->getTimestamp()),

          'timezone' => $station->getTimezone(),
          'file_md5' => $station->getFileMd5(),
          'file_name' => $station->getFileName(),
          'file_type' => $station->getFileType(),
          'path_to_file' => $station->getPathToFile(),
          'storage_id' => $station->getStorageId(),
          'simple_file_type' => $station->getSimpleFileType(),
          'location' => array('type' => 'Point', 'coordinates' => array(floatval($station->getLongitude()), floatval($station->getLatitude()) )),
          'file_size' => intval($station->getFileSize()),
          
          'group_ids' => $station->getGroupIds(),
          'read_permissions' => $station->getReadPermissions(),
          'write_permissions' => $station->getWritePermissions(),
          
          
          'created_at' => intval($station->getCreatedAtTimestamp()),
          'modified_at' => intval($station->getModifiedAtTimestamp())
        );
        
        return $document;
        
    }
    
}