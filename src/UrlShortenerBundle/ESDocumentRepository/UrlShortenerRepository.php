<?php

namespace UrlShortenerBundle\ESDocumentRepository;

use Elastica\Client;
use Elastica\Index;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;
use Elastica\Type;

class UrlShortenerRepository {
    /** @var Client $client */
    private $client;

    /** @var Index $index */
    private $index;

    /** @var string $index_name */
    private $indexName;

    /** @var Type $type */
    private $type;

    /** @var string $type_name */
    private $typeName;

    public function __construct($client, $indexName, $typeName)
    {
        $this->client = $client;
        $this->indexName = $indexName;
        $this->typeName = $typeName;
    }

    /**
     * @param string $slug
     * @param int    $from
     * @param int    $size
     *
     * @return ResultSet
     */
    public function getUrlBySlug($slug, $from = 0, $size = 20)
    {
        $search = new Search($this->client);
        $search->addIndex($this->getIndex());
        $search->addType($this->getType());

        $query = new Query([
            'query' => [
                'term' => ['slug' => $slug],
            ],
        ]);
        $query->setFrom($from);
        $query->setSize($size);

        $search->setQuery($query);

        $result = $search->search();

        return $result;
    }

    /**
     * Return the search index.
     *
     * @return Index
     */
    private function getIndex()
    {
        if (empty($this->index)) {
            $this->index = $this->client->getIndex($this->indexName);
        }

        return $this->index;
    }

    /**
     * Return the search type.
     *
     * @return Type
     */
    private function getType()
    {
        if (empty($this->type)) {
            $this->type = $this->getIndex()->getType($this->typeName);
        }

        return $this->type;
    }
}