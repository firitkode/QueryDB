function query_db($TYPE,$DB_TABLE,$DB_WHERECLAUSE,$DB_WHERECLAUSEEQUALTO,$TO_BE_FETCHED,$DB_ORDERBY,$DB_GROUPBY = NULL,$INSERT_ITEMS = NULL,$INSERT_VALUES = NULL,$DB_ORDERBY_ORDER = NULL)
{
	/* 
	FUNCTION CREATED BY: Gavin Sao
	
	PURPOSE: To make querying the database easier and quicker without having to monotonously 
			 display the code to query every single time. 
	
	RETURNS: *If using SELECT: A comma separated list with trailing comma
			 *If using INSERT: Nothing
			 *If using UPDATE: Nothing
			 *If using DELETE: Nothing
			 *If using TRUNCATE: Nothing
	
	OPTIONS: TYPE 	  			   : The type of query 
									 - can except:
												  INSERT
									              SELECT
												  UPDATE
												  DELETE
												  TRUNCATE
												  
			 DB_TABLE 			   : The table to be queried
			 DB_WHERECLAUSE 	   : Optional: The where clause (leave blank to retrieve all info)
			 					   : Can Except multiple options (comma-separated)
			 DB_WHERECLAUSEEQUALTO : Optional: The where clause is equal to (ditto)
			 					   : Can Except multiple options (comma-separated)			 
			 TO_BE_FETCHED		   : The column name to be fetched from the database
			 DB_ORDERBY			   : What to order by
			 DB_GROUPBY			   : Will tack on the group by to the query
			 $INSERT_ITEMS		   : Used for INSERT to insert items (can be comma-separated for 
									 multiple). Can be used for UPDATE or DELETE as well
			 $INSERT_VALUES		   : Used for INSERT to insert item values (can be comma-separated for 
								     multiple). Can be used for UPDATE or DELETE as well
	
	USAGE: Using this function is very simple, just simply call to action this:
			
		"query_db($TYPE,$DB_TABLE,$DB_WHERECLAUSE,$DB_WHERECLAUSEEQUALTO,$TO_BE_FETCHED);"
		
		   while connecting it to a $variable (or else it won't really be of any use) and
		   replacing all the parameters with their respective items (see USAGE above for help).
		   
	NOTES: 
			* This function requires that you use mysqli (only one you can use; mysql was dep'd)
			* This function assumes your mysqli variable/object name is "dba". To use another name,
			  you must change "global $dba;" and anywhere else "$dba" is found to what you have. 
			* If you would like to use a pre-made mysqli connect statement, you can head over to
			  mrgavinsao.com to checkout our snippets section and download the 
			  snippet-php-connect.zip file.
			  
	UPDATES: 
				2016-08-14 : * Added TRUNCATE functionality
				
	            2016-01-07 : * Added UPDATE and DELETE functionality
							 * Updated comments
				
				2015-05-31 : * Added an order by parameter
							 * Added ability to have multiple WHERE options
				
				2015-05-27 : * Changed "case 'WHERE':" to "case 'SELECT':"
							 * Added INSERT, UPDATE, DELETE "shell" cases; will add support for
							   them later
							   
				2015-05-26 : This script is born!
	*/
	global $dba;
	$return="";
	$FETCHED="";
	switch($TYPE){
		case 'INSERT':
			$sql = "INSERT INTO ".$DB_TABLE." (".$INSERT_ITEMS.")
VALUES (".$INSERT_VALUES.")";
			
			if(!$result = $dba->query($sql)){
				/* SOMETHING HAPPENED; QUERY WENT WONKY */
				$result = $dba->error." <br /><br /> SQL: ".$sql;
				echo $result;
			} else {
				
			}
		break;
		case 'SELECT':
			//check to see if WHERE clause is not blank				
			if($DB_WHERECLAUSE!=""){
				// CHECKING FOR SPECIFICS
				// -- check to see if there are multiple where options
				// first explode the where
				$DB_WHERECLAUSE_LIST		= explode(",",$DB_WHERECLAUSE);
				$DB_WHERECLAUSEEQUALTO_LIST	= explode(",",$DB_WHERECLAUSEEQUALTO);
				
				//now check to see if there are more than 1
				if((count($DB_WHERECLAUSE_LIST) > 1) && (count($DB_WHERECLAUSEEQUALTO_LIST > 1))){
					/* MULTIPLE WHERES */
					$sql = "SELECT * FROM `".$DB_TABLE."`";			
					for($i=0; $i<count($DB_WHERECLAUSE_LIST)-1; $i++){						
						//first or extra? if first : WHERE ; else : AND
						if($i==0){$sql .= "WHERE ";}
						if($i>0){$sql .= " AND ";}					
						
						// Find out if there is a :COMMA: found in the string 
						$pos = strpos($DB_WHERECLAUSEEQUALTO_LIST[$i],":COMMA:");
						if ($pos != null)
						{
							// rewrite the where 
							$THEWHERE = str_replace(":COMMA:",",",$DB_WHERECLAUSEEQUALTO_LIST[$i]);
						}
						else
						{
							$THEWHERE = $DB_WHERECLAUSEEQUALTO_LIST[$i];
						}
						$sql .= "`".$DB_WHERECLAUSE_LIST[$i]."` ".$THEWHERE."";
					}
					if($DB_ORDERBY_ORDER!=NULL){
						if($DB_GROUPBY!=NULL){$sql .= " GROUP BY ".$DB_GROUPBY." ORDER BY ".$DB_ORDERBY." ".$DB_ORDERBY_ORDER."";}else{$sql .= " ORDER BY ".$DB_ORDERBY." ".$DB_ORDERBY_ORDER."";}
					} else {
						if($DB_GROUPBY!=NULL){$sql .= " GROUP BY ".$DB_GROUPBY." ORDER BY ".$DB_ORDERBY." ASC";}else{$sql .= " ORDER BY ".$DB_ORDERBY." ASC";}	
					}					
				} else {
					/* SINGLE WHERE */
					if($DB_ORDERBY_ORDER!=NULL){
						$sql = "
							SELECT * FROM `".$DB_TABLE."`
							WHERE `".$DB_WHERECLAUSE_LIST[0]."` = '".$DB_WHERECLAUSEEQUALTO_LIST[0]."' ORDER BY ".$DB_ORDERBY." ".$DB_ORDERBY_ORDER."
						";
					} else {
						$sql = "
							SELECT * FROM `".$DB_TABLE."`
							WHERE `".$DB_WHERECLAUSE_LIST[0]."` = '".$DB_WHERECLAUSEEQUALTO_LIST[0]."' ORDER BY ".$DB_ORDERBY." ASC
						";
					}
				}
			} else {
				if($DB_ORDERBY_ORDER!=NULL){
					$sql = "
						SELECT * FROM `".$DB_TABLE."` ORDER BY ".$DB_ORDERBY." ".$DB_ORDERBY_ORDER."
					";
				} else {
					$sql = "
						SELECT * FROM `".$DB_TABLE."` ORDER BY ".$DB_ORDERBY." ASC
					";
				}
			}
			
			
			if(!$result = $dba->query($sql)){
				/* SOMETHING HAPPENED; QUERY WENT WONKY */
				$result = $dba->error." <br /><br /> SQL: ".$sql."<br /><br />WHERE CLAUSE: ".$DB_WHERECLAUSE."<br /><br />WHERE CLAUSE EQUAL TO: ".$DB_WHERECLAUSEEQUALTO;
				echo $result;
			} else {
				/* GOOD QUERY; DO SHIT */
				
				/* OH THIS IS ALL DEBUG SHIT...DO NOT UNCOMMENT THIS
				*/
				//echo "WHERE: ".$DB_WHERECLAUSE;
				//echo "<br />WHERE = TO: ".$DB_WHERECLAUSEEQUALTO;
				//echo "<br />TBF: ".$TO_BE_FETCHED;
				//echo "<br />SQL: ".$sql;
				
				while($FETCH=$result->fetch_assoc()){
					$FETCHED.=$FETCH[$TO_BE_FETCHED].",";
					$return=$FETCHED;
				}  
			}
			
			@$result->free();
		break;
		case 'UPDATE':
			//check to see if WHERE clause is not blank				
			if($DB_WHERECLAUSE!=""){
				// CHECKING FOR SPECIFICS
				// -- check to see if there are multiple where options
				// first explode the where
				$DB_WHERECLAUSE_LIST		= explode(",",$DB_WHERECLAUSE);
				$DB_WHERECLAUSEEQUALTO_LIST	= explode(",",$DB_WHERECLAUSEEQUALTO);
				
				//now check to see if there are more than 1
				if((count($DB_WHERECLAUSE_LIST) > 1) && (count($DB_WHERECLAUSEEQUALTO_LIST > 1))){
					/* MULTIPLE WHERES */
					$sql = "UPDATE `".$DB_TABLE."` SET ".$INSERT_ITEMS." = '".$INSERT_VALUES."' ";			
					for($i=0; $i<count($DB_WHERECLAUSE_LIST)-1; $i++){						
						//first or extra? if first : WHERE ; else : AND
						if($i==0){$sql .= "WHERE ";}
						if($i>0){$sql .= " AND ";}						
						$sql .= "`".$DB_WHERECLAUSE_LIST[$i]."` ".$DB_WHERECLAUSEEQUALTO_LIST[$i]."";
					}		
				} else {
					/* SINGLE WHERE */
					$sql = "
						UPDATE `".$DB_TABLE."` SET ".$INSERT_ITEMS." = '".$INSERT_VALUES."' WHERE `".$DB_WHERECLAUSE_LIST[0]."` = '".$DB_WHERECLAUSEEQUALTO_LIST[0]."'
					";
				}
			} else {
				$sql = "
					UPDATE `".$DB_TABLE."` SET ".$INSERT_ITEMS." = '".$INSERT_VALUES."'
				";
			}
			
			//MySqli Update Query
			$results = $dba->query($sql);
			//MySqli Delete Query
			//$results = $dba->query("DELETE FROM products WHERE ID=24");
			if($results){
				//echo 'Success! record updated / deleted'; 
			}else{
				echo 'Error : ('. $dba->errno .') '. $dba->error.'<br />';
				echo "WHERE: ".$DB_WHERECLAUSE;
				echo "<br />WHERE = TO: ".$DB_WHERECLAUSEEQUALTO;
			}
		break;
		case 'DELETE':
			//check to see if WHERE clause is not blank				
			if($DB_WHERECLAUSE!=""){
				// CHECKING FOR SPECIFICS
				// -- check to see if there are multiple where options
				// first explode the where
				$DB_WHERECLAUSE_LIST		= explode(",",$DB_WHERECLAUSE);
				$DB_WHERECLAUSEEQUALTO_LIST	= explode(",",$DB_WHERECLAUSEEQUALTO);
				
				//now check to see if there are more than 1
				if((count($DB_WHERECLAUSE_LIST) > 1) && (count($DB_WHERECLAUSEEQUALTO_LIST > 1))){
					/* MULTIPLE WHERES */
					$sql = "DELETE FROM `".$DB_TABLE."`";			
					for($i=0; $i<count($DB_WHERECLAUSE_LIST)-1; $i++){						
						//first or extra? if first : WHERE ; else : AND
						if($i==0){$sql .= "WHERE ";}
						if($i>0){$sql .= " AND ";}						
						$sql .= "`".$DB_WHERECLAUSE_LIST[$i]."` ".$DB_WHERECLAUSEEQUALTO_LIST[$i]."";
					}		
				} else {
					/* SINGLE WHERE */
					$sql = "
						DELETE FROM `".$DB_TABLE."` WHERE `".$DB_WHERECLAUSE_LIST[0]."` = '".$DB_WHERECLAUSEEQUALTO_LIST[0]."'
					";
				}
			} else {
				$sql = "
					DELETE FROM `".$DB_TABLE."`
				";
			}
			
			//MySqli Update Query
			$results = $dba->query($sql);
			//MySqli Delete Query
			//$results = $dba->query("DELETE FROM products WHERE ID=24");
			if($results){
				//echo 'Success! record updated / deleted'; 
			}else{
				echo 'Error : ('. $dba->errno .') '. $dba->error.'<br />';
				echo "WHERE: ".$DB_WHERECLAUSE;
				echo "<br />WHERE = TO: ".$DB_WHERECLAUSEEQUALTO;
			}
		break;
		case 'TRUNCATE':
			$sql = "
				TRUNCATE TABLE `".$DB_TABLE."`
			";
			
			//MySqli Update Query
			$results = $dba->query($sql);
			//MySqli Delete Query
			//$results = $dba->query("DELETE FROM products WHERE ID=24");
			if($results){
				//echo 'Success! record updated / deleted'; 
			}else{
				echo 'Error : ('. $dba->errno .') '. $dba->error.'<br />';
				echo "WHERE: ".$DB_WHERECLAUSE;
				echo "<br />WHERE = TO: ".$DB_WHERECLAUSEEQUALTO;
			}
		break;
	}
	return $return;
}
