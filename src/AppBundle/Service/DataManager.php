<?php
/**
 * Created by PhpStorm.
 * User: piripasa
 * Date: 28/4/18
 * Time: 3:35 PM
 */

namespace AppBundle\Service;


use GuzzleHttp\Client;

class DataManager
{
    private $baseUrl;
    private $routeParam;
    private $searchQuery;
    private $clientId;
    private $clientSecret;
    private $username;
    private $data = [];
    private $requestType = null;
    private $page;
    private $perPage;

    protected function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    private function getBaseUrl()
    {
        return $this->baseUrl;
    }

    protected function setRouteParam($param)
    {
        $this->routeParam = $param;
        return $this;
    }

    private function getRouteParam()
    {
        return $this->routeParam;
    }

    protected function setSearchQuery($query)
    {
        $this->searchQuery = $query;
        return $this;
    }

    private function getSearchQuery()
    {
        return $this->searchQuery;
    }

    protected function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    private function getClientId()
    {
        return $this->clientId;
    }

    protected function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    private function getClientSecret()
    {
        return $this->clientSecret;
    }

    protected function resetSettings()
    {
        $this->setPage(1)->setData([])->setArrayData([]);
        return $this;
    }

    protected function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    private function getUsername()
    {
        return $this->username;
    }

    protected function setRequestType($type)
    {
        $this->requestType = $type ? '/' . $type : '';
        return $this;
    }

    private function getRequestType()
    {
        return $this->requestType;
    }

    protected function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    private function getPage()
    {
        return $this->page;
    }

    protected function setPerPage($perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }

    private function getPerPage()
    {
        return $this->perPage;
    }

    private function getUrl()
    {
        $url = $this->getBaseUrl() . $this->getRouteParam();

        if ($this->getRouteParam() == 'search/')
            $url .= $this->getRequestType();
        else
            $url .= $this->getUsername() . $this->getRequestType();

        return preg_replace('/([^:])(\/{2,})/', '$1/', $url);
    }

    protected function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    protected function setArrayData($data)
    {
        foreach ($data as $key => $value) {
            array_push($this->data, $value);
        }
        return $this;
    }

    protected function getData()
    {
        return $this->data;
    }

    protected function processRequest()
    {
        $client = new Client();
        $params = [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
        ];

        if ($this->getRequestType()) {
            $params['per_page'] = $this->getPerPage();
            $params['page'] = $this->getPage();
        }

        if ($this->getRouteParam() == 'search/') {
            $params['q'] = $this->getSearchQuery();
        }

        try {
            $response = $client->get($this->getUrl() . '?' . urldecode(http_build_query($params)));
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if ($this->getRequestType()) {

                    if (array_key_exists('items', $data)) {

                        $data = $data['items'];
                    }

                    $this->setArrayData($data);

                    if (count($data) == $this->getPerPage()) {
                        $this->setPage($this->getPage() + 1)->processRequest();
                    }
                } else {
                    $this->setData($data);
                }
            }
        } catch (\Exception $exception) {
            //return $exception->getCode();
            //throw new $exception;
        }
        return $this;
    }
}
