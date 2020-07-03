<?php
namespace App;

use \MongoDB\Client;
use \MongoDB\BSON\UTCDateTime;
class Database
{
	function __construct()
	{
		$dbname = env("MONGO_DB_NAME", "localshipments");
		$server = env("MONGO_DB_SERVER", "mongodb://127.0.0.1");
		
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