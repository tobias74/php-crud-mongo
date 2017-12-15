<?php
namespace Zeitfaden\MongoDb;


class StationsIterator implements \Iterator {
    private $mongoCursor;
    private $stationMapper;

    public function __construct($mongoCursor, $stationMapper) 
    {
        $this->mongoCursor =  new \IteratorIterator($mongoCursor);
        $this->mongoCursor->rewind();
        $this->stationMapper = $stationMapper;
    }

    function rewind() {
        $this->mongoCursor->rewind();
    }

    function current() {
        $currentItem = $this->mongoCursor->current();
        return $this->mapToStation($currentItem);
    }

    function key() {
        return $this->mongoCursor->key();
    }

    function next() {
        $this->mongoCursor->next();
    }

    function valid() {
        return $this->mongoCursor->valid();
    }
    
    protected function mapToStation($currentItem)
    {
        return $this->stationMapper->instantiateStation($currentItem);
    }
}