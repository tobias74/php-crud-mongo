<?php
namespace Zeitfaden\MongoDb;


class UserRepository 
{

    protected $connection = false;
    
    use \Zeitfaden\Traits\ConfigGetter;
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
    

    protected function produceUserObject()
    {
        return new \Zeitfaden\Model\User();    
    }
    
    protected function instantiateUser($userDocument)
    {
        $user = $this->produceUserObject();
        $user->setId($userDocument['id']);
        $user->setAuth0Id($userDocument['auth0Id']);
        $user->profileImage = isset($userDocument['profileImage']) ? $userDocument['profileImage'] : false;
        $user->displayName = isset($userDocument['displayName']) ? $userDocument['displayName'] : false;

        return $user;
    }
    

    public function getUserById($id)
    {
        $dbName = $this->getMongoDbName();
        $users = $this->getConnection()->$dbName->users;
        $userDocument = $users->findOne(array('id' => $id));
        if (!$userDocument)
        {
            throw new \Zeitfaden\Exception\NoMatchException();
        }
        return $this->instantiateUser($userDocument);
    }
    
    public function getUserByAuth0Id($id)
    {
        $dbName = $this->getMongoDbName();
        $users = $this->getConnection()->$dbName->users;
        $userDocument = $users->findOne(array('auth0Id' => $id));
        if (!$userDocument)
        {
            throw new \Zeitfaden\Exception\NoMatchException();
        }
        return $this->instantiateUser($userDocument);
    }


    public function introduceUserByAuth0Id($id)
    {
        $dbName = $this->getMongoDbName();
        
        $users = $this->getConnection()->$dbName->users;

        $userDocument = array(
            'id' => 'u'.$this->getUniqueId(),
            'auth0Id' => $id,
            'profileImage' => '',
            'displayName' => ''
        );
        
        $users->insertOne($userDocument);
        
        return $this->instantiateUser($userDocument);
    }
    
    public function updateUser($user)
    {
        $dbName = $this->getMongoDbName();
        $users = $this->getConnection()->$dbName->users;
        $userDocument = array(
            'id' => $user->id,
            'auth0Id' => $user->auth0Id,
            'profileImage' => $user->profileImage,
            'displayName' => $user->displayName
        );
        
        $users->updateOne(array('id' => $user->id, 'auth0Id' => $user->auth0Id), array('$set' => $userDocument), array('upsert' => true));
    }


}