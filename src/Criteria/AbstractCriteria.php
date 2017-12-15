<?php 
namespace PhpCrudMongo\Criteria;

interface CriteriaInterface
{
	public function affectsField($field);
	public function logicalAnd($criteria);
	public function logicalOr($criteria);
	public function logicalNot();
	
}


abstract class AbstractCriteria implements CriteriaInterface
{
  private static $keycount=0;
  private $key;
    
  final public function getKey() 
  {
      if ( ! isset( $this->key ) ) 
      {
          self::$keycount++;
          $this->key=self::$keycount;
      }
      return $this->key;
  }
      
	final public function logicalAnd($criteria)
	{
		return new AndCriteria($this, $criteria);
	}

	final public function logicalOr($criteria)
	{
		return new OrCriteria($this, $criteria);
	}
	
	final public function logicalNot()
	{
		return new NotCriteria($this);
	}

  public function affectsField($field)
  {
      return false;  
  }
  

}
