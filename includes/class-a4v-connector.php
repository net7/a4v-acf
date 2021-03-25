<?php

/**
 * Fired during plugin activation
 *
 * @link       www.netseven.it
 * @since      1.0.0
 *
 * @package    A4v
 * @subpackage A4v/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    A4v
 * @subpackage A4v/includes
 * @author     Netseven <aiola@netseven.it>
 */
class A4v_Connector {

	public function __construct($endpoint = "", $token = "") {
		$this->endpoint = $endpoint;	
		$this->token = $token;	
	}

	public function get_resources($params = []){ 
		$result = [];

        $offset = isset($params['paged']) ? ($params['paged'] - 1) * $params['limit'] : 0;
        $query = <<<GRAPHQL
		query {
			
            search(
                searchParameters: {
                    totalCount: 0
                    gallery: false
                    facets: [
                        { id: "query", type: "value" }
                        {
                            id: "query-all"
                            type: "value"
                            data: [{ value: "1", label: "Cerca in tutti i campi delle schede" }]
                        }
                        { id: "query-links", type: "value" }
                        {
                            id: "entity-types"
                            type: "value"
                            operator: "OR"
                            limit: 10
                            order: "count"
                        }
                        { id: "resource-id", type: "value" }
                        { id: "doc-classification", type: "value" }
                    ]
                    page: { offset:$offset, limit: {$params['limit']} }
                    results: {
                        order: { type: "score", key: "_score", direction: "DESC" }
                        fields: [{ id: "description", highlight: true, limit: 200 }]
                    }
                    filters: [
                        {
                            facetId: "query"
                            value: "{$params['s']}"
                            searchIn: [{ key: "label.ngrams", operator: "LIKE" }]
                        }
                        {
                            facetId: "query-all"
                            value: null
                            searchIn: [{ key: "label.ngrams^5,text^4,fields.*^3", operator: "=" }]
                        }
                        {
                            facetId: "query-links"
                            value: []
                            searchIn: [{ key: "source.entityType", operator: "=" }]
                        }
                        {
                            facetId: "resource-id"
                            value: "{$params['id']}"
                            searchIn: [{ key: "id_arianna", operator: "LIKE" }]
                        }
                        {
                            facetId: "doc-classification"
                            value: "{$params['type']}"
                        }
                    ]
                }
            ) {
                totalCount
                results {                        
                    fields {
                        id
                        highlight
                        limit
                    }
                    items {
                        ... on Entity {
                            id
                            label
                            typeOfEntity
                            parent_type
                            document_type
                        }
                        ... on Item {
                            id
                            label                               
                            document_type
                            document_classification
                            parent_type
                            image
                        }
                    }
                }
                facets {
                    id
                    type
                    data {
                        label
                        value
                    }
                }
            }                   
		}
GRAPHQL;
        $result = $this->graphql_query($this->endpoint, $query);
        return $result;
	}

    public function get_resource_by_id($ids = []) {

        $ids_string =array_map(function($i){return '"'.$i.'"';}, $ids);
        $ids_string = join(",", $ids_string);
        $query = <<<GRAPHQL
		    query {
                getResourceById(id: [$ids_string]) {  
                    ... on Entity {
                        id
                        label
                        typeOfEntity
                        parent_type         
                        document_type               
                    }
                    ... on Item {
                        id
                        label                               
                        document_type
                        document_classification
                        parent_type
                        image
                    }
                }
            }
GRAPHQL;
        $result = $this->graphql_query($this->endpoint, $query);
        return $result;
    }

/** 
	 * A curl wrapper to send graphql queries to Apollo Server 
	 * returns always a json with data and errors
	 **/
	protected function graphql_query(string $endpoint, string $query, array $variables = [], string $token = null) {
		$headers = ['Content-Type: application/json', 'User-Agent: Dunglas\'s minimal GraphQL client'];
		if (null !== $token) {
			$headers[] = "Authorization: bearer " . $token;
		}

		try{
			$content = json_encode(['query' => $query, 'variables' => $variables]);
			$data = $this->curl( $endpoint, $headers , $content );
			$json_decoded = json_decode($data, true);
		}
		catch( Exception $e )
		{
			//graphQl way to return errors
			$json_decoded = [
				'errors' => [
					[
						'message' => $e->getMessage()
					]
				]
			 ];
		}
		finally
		{
			//print errors in php error log and in a footer errors wrapper(shown by modal)
			$this->gqErrorsHandler($json_decoded);
		}

		return $json_decoded;
	}

	 /**
     * Curl wrapper for this class
     * @param $url
     * @param array $headers
     * @param string $body
     * @return array|bool|false|mixed|string|void
     */
    protected function curl( $url , $headers = [], $body = '') {

        // Create a new cURL resource
        $curl = curl_init();

        if (!$curl) {
            throw new Exception("Couldn't initialize a cURL handle");
        }

        // Set the file URL to fetch through cURL
        curl_setopt($curl, CURLOPT_URL, $url);

        // Set a different user agent string (Googlebot)
        //curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');

        // Follow redirects, if any
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // Fail the cURL request if response code = 400 (like 404 errors)
        curl_setopt($curl, CURLOPT_FAILONERROR, false);

        // Return the actual result of the curl result instead of success code
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Wait for 10 seconds to connect, set 0 to wait indefinitely
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

        // Execute the cURL request for a maximum of 10 seconds
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        // Do not check the SSL certificates
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // Set Headers of request
        if ( ! empty( $headers ) )
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // Set POST request if body parameter is provided
        if ( ! empty( $body ) )
        {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }


        // Fetch the URL and save the content in $json variable
        $json = curl_exec($curl);


        // Check if any error has occurred
        if (curl_errno($curl))
        {
            // Format error in GraphQl way
            $json = [
				'data' => [],
				'errors' => [
					[
						'message' => curl_error($curl),
						'type' => 'curl error'
					]
				],
                
            ];

            $json = json_encode($json);
        }

        // close cURL resource to free up system resources
        curl_close($curl);

        return $json;
    }

	
	/** 
    * A simple wrapper for graphQl singular(only 1 method) queries
    */
    protected function gqSingularQuery( $query , $method , $fragments = '', $variables = [], $after_query = '' ) {
        $gq_query = <<<GRAPHQL
		query $after_query{
			$query
		}		  
GRAPHQL;
        $response = $this->graphql_query($this->endpoint, $gq_query.$fragments,$variables);
        return $this->theGraphQlResponse($response, $method);
	}

	/** 
    * A simple wrapper for graphQl singular(only 1 method) mutation
    */
    protected function gqSingularMutation( $query , $method , $fragments = '',$variables = [], $after_mutation = '') {
        $gq_query = <<<GRAPHQL
		mutation $after_mutation{
			$query
		}		  
GRAPHQL;
        $response = $this->graphql_query($this->endpoint, $gq_query.$fragments, $variables);
        return $this->theGraphQlResponse($response, $method);
	}
	
	

    /** 
     * A simple error Handler
    */
    protected function gqErrorsHandler( $response )
    {
        if ( isset($response['errors']) && count($response['errors']) > 0 )
        {
            foreach( $response['errors'] as $error )
            {
                $type = isset( $error['type'] ) ? $error['type'] : false;
				$msg = isset( $error['message'] ) ? $error['message'] : false;
				if ( $msg )
                 $error = $msg; //new PrimisConnectorException( $msg );
            }
        }
    }

    /** 
    * Reduce the graphQl responses to necessary data (supports only 1 method)
    */
    protected function theGraphQlResponse( $response , $method )
    {
		$default_response = array( 
			'errors' => isset( $response['errors'] ) ? $response['errors'] : [],
			'data' => isset( $response['data'][$method] ) ? $response['data'][$method] : []
		);
        return $default_response;
    }
}
