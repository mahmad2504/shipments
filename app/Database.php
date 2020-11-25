<?php
namespace App;

use \MongoDB\Client;
use \MongoDB\BSON\UTCDateTime;
class Database
{
	function __construct($server,$db)
	{
		$dbname = $db;
		$server = $server;
		
		$mongoClient=new Client($server);
		$this->db = $mongoClient->$dbname;
		
	}
	public function Update($query,$obj)
	{
		$options=['upsert'=>true];
		$this->db->tickets->updateOne($query,['$set'=>$obj],$options);
	}
	public function ReadActive()
	{
		$date = new \DateTime('-7 days');
		$query =['$or' => [ ['progress' => ['$ne' =>100]],['due' => ['$gt' => $date->format('Y-m-d')]]]];
		//$query =['dayLastActivity' => ['$gt' => '2020-07-01']];

		return $this->Read($query,['due' => 1],[]);
	}
	public function ReadAll()
	{
		$active =  $this->Read(['list'=> ['$nin' =>['Expense']]],['dateLastActivity' => -1],[]);
		$date = new \DateTime('-6 days');
		$closed = $this->Read(['list'=>'Expense','dayLastActivity'=>['$gt' => $date->format('Y-m-d')]  ],['dateLastActivity' => -1],[]);
	    return array_merge($active->toArray(),$closed->toArray());
	}
	public function Read($query,$sort=[],$projection=[],$limit=-1)
	{
		$query = $query;
		$options = ['sort' => $sort,
					'projection' => $projection,
					];
		if($limit != -1)
			$options['limit'] = $limit;
		
		$cursor = $this->db->tickets->find($query,$options);
		return $cursor;
	}
	
}