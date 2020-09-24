<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;

class MetsisSearchController extends ControllerBase {

    public function getChildrenCount() {
      $query_from_request = \Drupal::request()->query->all();
      $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
      $id = $params['metadata_identifier'];

      /** @var Index $index  TODO: Get the index name from some config */
      $index = Index::load('drupal8');

      /** @var SearchApiSolrBackend $backend */
      $backend = $index->getServerInstance()->getBackend();

      $connector = $backend->getSolrConnector();

      $solarium_query = $connector->getSelectQuery();
      $solarium_query->setQuery('related_dataset:'.$id);
      //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
      $solarium_query->setRows(1);

      $result = $connector->execute($solarium_query);

      // The total number of documents found by Solr.
      $found = $result->getNumFound();

      // The total number of documents returned from the query.
      //$count = $result->count();

      // Check the Solr response status (not the HTTP status).
      // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
      //$status = $result->getStatus();

      // An array of documents. Can also iterate directly on $result.
      //$documents = $result->getDocuments();


      $data = [
        'success' => true,
        'count' => $found,
      ];
      \Drupal::logger('metsis_search')->debug("MetsisSearchController::getChildrenCount: " .$count . ", found: ". $found );
       return new \Drupal\Core\Ajax\AjaxResponse(Json::encode($data));
   }
}
