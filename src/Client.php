<?php

/*
 * This file is part of flagrow/flarum-api-client.
 *
 * Copyright (c) Flagrow.
 *
 * http://flagrow.github.io
 *
 * For the full copyright and license information, please view the license.md
 * file that was distributed with this source code.
 */

namespace Flagrow\Flarum\Api;

use GuzzleHttp\Client as Guzzle;

class Client
{

    /**
     * Flarum user token.
     *
     * @var string
     */
    protected $token;

    /**
     * @var
     */
    protected $guzzle;

    /**
     * API endpoint of the Flarum installation.
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * Client constructor.
     *
     * @param string $apiUrl
     * @param null   $token
     * @param array  $options
     */
    public function __construct($apiUrl = 'https://discuss.flarum.org/api/', $token = null, $options = [])
    {
        $options = array_merge([
            'base_uri' => $apiUrl,
            'headers'  => [
                'User-Agent'   => 'Flagrow/Flarum/Api/Client',
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ], $options);

        if (!empty($token)) {
            $options['headers']['Authorization'] = 'Token ' . $token;
        }

        $this->guzzle = new Guzzle($options);
    }

    protected function request($method = 'get', $url, $options = [])
    {
        /** @var \Psr\Http\Message\ResponseInterface $result */
        $result = $this->guzzle->{$method}($url, $options);

        switch($result->getStatusCode())
        {
            case 200:
            case 201:
                return json_decode($result->getBody(), true);
                break;
        }

        // let's keep this debugger friend here for now
        // mark as @todo
        dd($result->getStatusCode());
    }

    /**
     * Loads one or a set of the specified type.
     *
     * @param       $type
     * @param null  $id
     * @param array $options
     * @return mixed
     */
    public function load($type, $id = null, $options = [])
    {
        return $this->request('get', $type . ($id ? '/' . $id : null), $options);
    }

    /**
     * Creates an object of the specified type.
     *
     * @param       $type
     * @param array $attributes
     * @param array $options
     * @return mixed
     */
    public function create($type, $attributes = [], $options = [])
    {
        return $this->request('post', $type, [
            'json' => [
                'data' => [
                    'type'       => $type,
                    'attributes' => $attributes
                ]
            ]
        ]);
    }

    /**
     * Loads a subset of discussions or an Id.
     *
     * @param null $id
     * @return array
     */
    public function discussions($id = null)
    {
        return $this->load('discussions', $id);
    }

    /**
     * Creates a new tag.
     *
     * @param        $name
     * @param        $slug
     * @param string $description
     * @param string $color
     * @param bool   $isHidden
     * @return array
     */
    public function createTag($name, $slug, $description = '', $color = '', $isHidden = false)
    {
        return $this->create('tags', compact('name', 'slug', 'description', 'color', 'isHidden'));
    }
}
