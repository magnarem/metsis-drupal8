<?php
/*
 *
 * @file
 * Contains \Drupal\metsis_lib\SearchUtils
 *
 * utility functions for metsis_lib
 *
 **/
namespace Drupal\metsis_lib;


use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Select\Result\Document;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\RedirectResponse;


class Searchutils
{

  /**
   * @var ConfigEntityInterface $account
   */
    protected $config;

    /**
     * Class constructor.
     */
    public function __construct(ConfigEntityInterface $config)
    {
        $this->config = $config->get('metsis_lib.settings');
    }


    /**
     * Get OD variables from OPeNDAP parser service
     */
    public function adc_get_od_global_attributes($metadata_identifier, $collection_core)
    {
        /**
         * Get the OPeNDAP parser service config
         */
         $od_server_ip = $this->config->get('metsis_opendap_parser_ip');
         $od_server_port = $this->config->get('metsis_opendap_parser_port');
         $od_server_service = $this->config->get('metsis_opendap_parser_service');

         //Create uri from config:
         $uri = $od_server_ip . ':' . $od_server_port . $od_server_ip;


         //Get the referer:
         $request = \Drupal::request();
         $referer = $request->headers->get('referer');

        $odquery = '{
                  findAllAttributes(
                    datasetId: "' . $metadata_identifier . '", collection: "' . $collection_core . '"
                      ) {
                          name value

                      }
                 }';


        try {
            $client = \Drupal::httpClient();
            $request = $client->request('GET', $uri, [
                       'query' =>$odquery,

                     ],
                   ]);

            $responseStatus = $request->getStatusCode();
            $data = $request->getBody();
            $json_response = \Drupal\Component\Serialization\Json::decode($data);
            return ($json_response);
        } catch (Exception $e) {
            \Drupal::messenger()->addError("Service call did not succeed. Ensure that the dataset resource URL is correct.");
            \Drupal::messenger()->addError($uri);
            \Drupal::messenger()->addError(t(
                "If the dataset resource URL is correct, the @link is wrong. Please check.",
                array('@link' => \Drupal\Core\Link::fromTextAndUrl(
                         'backend service URL',
                         \Drupal\Core\Url::fromRoute('metsis_lib.admin_config_form')
                     )->toString())
            ));
            $response =  new RedirectResponse($referer);
            $response->send();
        }
      }

    public function adc_get_od_variables($metadata_identifier, $collection_core) {
      /**
       * Get the OPeNDAP parser service config
       */
       $od_server_ip = $this->config->get('metsis_opendap_parser_ip');
       $od_server_port = $this->config->get('metsis_opendap_parser_port');
       $od_server_service = $this->config->get('metsis_opendap_parser_service');

       //Create uri from config:
       $uri = $od_server_ip . ':' . $od_server_port . $od_server_ip;


       //Get the referer:
       $request = \Drupal::request();
       $referer = $request->headers->get('referer');

        $odquery = '{
                      findAllVariables(
                        datasetId: "' . $metadata_identifier . '", collection: "' . $collection_core . '"
                          ) {
                              name
                                   attributes {
                                     name value
                                      }
                          }
                     }';

       try {
           $client = \Drupal::httpClient();
           $request = $client->request('GET', $uri, [
                      'query' =>$odquery,

                    ],
                  ]);

           $responseStatus = $request->getStatusCode();
           $data = $request->getBody();
           $json_response = \Drupal\Component\Serialization\Json::decode($data);
           return ($json_response);
       } catch (Exception $e) {
           \Drupal::messenger()->addError("Service call did not succeed. Ensure that the dataset resource URL is correct.");
           \Drupal::messenger()->addError($uri);
           \Drupal::messenger()->addError(t(
               "If the dataset resource URL is correct, the @link is wrong. Please check.",
               array('@link' => \Drupal\Core\Link::fromTextAndUrl(
                        'backend service URL',
                        \Drupal\Core\Url::fromRoute('metsis_lib.admin_config_form')
                    )->toString())
           ));
           $response =  new RedirectResponse($referer);
           $response->send();
       }

      }

      public function msb_get_fields($metadata_identifier, $fields)
      {
          /** @var Index $index  TODO: Change to metsis when prepeare for release */
          $index = Index::load('drupal8');

          /** @var SearchApiSolrBackend $backend */
          $backend = $index->getServerInstance()->getBackend();

          $connector = $backend->getSolrConnector();

          $solarium_query = $connector->getSelectQuery();

          foreach ($metadata_identifier as $id) {
              \Drupal::logger('metsis_wms')->debug("setQuery: metadata_identifier: " .$id);
              $solarium_query->setQuery('metadata_identifier:'.$id);
          }
          //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
          $solarium_query->setRows(2);
          $solarium_query->setFields($fields);

          $result = $connector->execute($solarium_query);

          // The total number of documents found by Solr.
          $found = $result->getNumFound();
          \Drupal::logger('metsis_wms')->debug("found :" .$found);
          // The total number of documents returned from the query.
          //$count = $result->count();

          // Check the Solr response status (not the HTTP status).
          // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
          //$status = $result->getStatus();

          // An array of documents. Can also iterate directly on $result.
          return $result;
      }

}
