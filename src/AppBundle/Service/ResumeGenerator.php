<?php
/**
 * Created by PhpStorm.
 * User: piripasa
 * Date: 28/4/18
 * Time: 1:20 PM
 */

namespace AppBundle\Service;

class ResumeGenerator extends DataManager
{
    private $username;

    public function __construct($username)
    {
        parent::setUsername($username);
        parent::setBaseUrl('https://api.github.com/');
        parent::setClientId('b9593f309491fc18d145');
        parent::setClientSecret('e71fee6cd37d7a3b6e36e6ae1d89217c2c92774f');
        parent::setPage(1);
        parent::setPerPage(100);

        $this->username = $username;
    }

    public function getProfile()
    {
        return $this->setRouteParam('users/')->processRequest()->getData();
    }

    public function getRepositories()
    {
        return $this->resetSettings()->setRouteParam('users/')->setRequestType('repos')->processRequest()->getData();
    }

    public function getOrganizations()
    {
        return $this->resetSettings()->setRouteParam('users/')->setRequestType('orgs')->processRequest()->getData();
    }

    public function getFavourites()
    {
        return $this->resetSettings()->setRouteParam('users/')->setRequestType('starred')->processRequest()->getData();
    }

    public function getIssues()
    {
        return $this->resetSettings()
            ->setRouteParam('search/')
            ->setRequestType('issues')
            ->setSearchQuery("type:pr+is:merged+author:{$this->username}")
            ->processRequest()
            ->getData();
    }

    public function getLanguages($repositories = [])
    {
        $languages = [];
        $languageSum = 0;
        foreach ($repositories as $repository) {
            if (array_key_exists($repository['language'], $languages)) {
                $languages[$repository['language']] = $languages[$repository['language']] + 1;
            } else {
                $languages[$repository['language']] = 1;
            }
            $languageSum ++;
        }

        return [$languages, $languageSum];
    }

    public function getContributions($issues = [])
    {
        $contributions = [];
        foreach ($issues as $issue) {
            if (array_key_exists($issue['repository_url'], $contributions)) {
                $contributions[$issue['repository_url']]['count'] = $contributions[$issue['repository_url']]['count'] + 1;

            } else {
                $repourl = str_replace('https://api.github.com/repos', 'https://github.com', $issue['repository_url']);
                $contributions[$issue['repository_url']] = [
                    'repository_name' => str_replace('https://github.com/', '', $repourl),
                    'repository_url' => $repourl,
                    'commits_url' => $repourl . '/commits?author=' . $this->username,
                    'username' => $this->username,
                    'count' => 1
                ];
            }
        }

        return $contributions;
    }


}