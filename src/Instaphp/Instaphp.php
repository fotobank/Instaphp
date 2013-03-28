<?php

/**
 * The MIT License (MIT)
 * Copyright © 2013 Randy Sesser <randy@instaphp.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the “Software”), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @author Randy Sesser <randy@instaphp.com>
 * @filesource
 */
namespace Instaphp;

/**
 * A PHP library for accessing Instagram's API
 * 
 * This is version 2 of the Instaphp library and is a complete rewrite from the
 * previous version. It's not entirely compatible with the previous version.
 * 
 * Requirements:
 *	- PHP >= 5.4.0 with cURL enabled
 *
 * @author Randy Sesser <randy@instaphp.com>
 * @license http://instaphp.mit-license.org MIT License 
 * @package Instaphp
 * @version 2.0-dev
 * 
 * @property-read Instagram\Media $media Media API
 * @property-read Instagram\Users $users Users API
 * @property-read Instagram\Tags $tags Tags API
 * @property-read Instagram\Locations $locations Locations API
 * @property-read Instagram\Subscriptions $subscriptions Subscription API
 */
class Instaphp
{
	/** @var array Storage for the endpoints */
	private static $endpoints = [];
	
	/** @var array Available enoints */
	private static $availableEndpoints = ["media", "users", "tags", "locations", "subscriptions"];
	
	/** @var array Configuration for Instaphp */
	protected $config = [];

	public function __construct(array $config = [])
	{
		$ua = sprintf('Instaphp/2.0; cURL/%s; (+http://instaphp.com)', curl_version()['version']);
		$defaults = [
			'client_id'	=> '',
			'client_secret' => '',
			'callback_uri' => '',
			'scope' => 'comments+relationships+likes',
			'api_protocol' => 'https',
			'api_host' => 'api.instagram.com',
			'api_version' => 'v1',
			'http_useragent' => $ua,
			'http_timeout' => 6,
			'http_connect_timeout' => 2,
		];
		$this->config = $config + $defaults;
	}
	
	public function __get($endpoint)
	{
		$endpoint = strtolower($endpoint);
		$class = ucfirst(strtolower($endpoint));
		if (in_array($endpoint, static::$availableEndpoints)) {
			if (!$this->__isset($endpoint)) {
				$ref = new \ReflectionClass('Instaphp\\Instagram\\' . $class);
				$obj = $ref->newInstanceArgs([$this->config]);
				static::$endpoints[$endpoint] = $obj;
			}
			
			return static::$endpoints[$endpoint];
		}
		throw new Exceptions\InvalidEndpointException("{$endpoint} is not a valid endpoint");
	}
	
	public function __isset($endpoint)
	{
		$endpoint = strtolower($endpoint);
		return in_array($endpoint, static::$availableEndpoints) && 
				isset(static::$endpoints[$endpoint]) && 
				static::$endpoints[$endpoint] instanceof Instagram\Instagram;
	}
	
	public function __unset($endpoint)
	{
		$endpoint = strtolower($endpoint);
		if (isset(static::$endpoints[$endpoint]))
			unset(static::$endpoints[$endpoint]);
	}
	
	/**
	 * Get the OAuth url for logging into Instagram
	 * @return string
	 */
	public function getOauthUrl()
	{
		return sprintf('%s://%s/oauth/authorize/?client_id=%s&redirect_uri=%s&scope=%s&display=touch&response_type=code',
				$this->config['api_protocol'],
				$this->config['api_host'],
				$this->config['client_id'],
				urlencode($this->config['redirect_uri']),
				$this->config['scope']);
	}
}

