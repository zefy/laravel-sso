<?php

namespace Zefy\LaravelSSO;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Zefy\SimpleSSO\SSOServer;
use Zefy\LaravelSSO\Resources\UserResource;
use Zefy\SimpleSSO\Exceptions\SSOServerException;

class LaravelSSOServer extends SSOServer
{
    /**
     * Redirect to provided URL with query string.
     *
     * If $url is null, redirect to url which given in 'return_url'.
     *
     * @param string|null $url URL to be redirected.
     * @param array $parameters HTTP query string.
     * @param int $httpResponseCode HTTP response code for redirection.
     *
     * @return void
     */
    protected function redirect(?string $url = null, array $parameters = [], int $httpResponseCode = 307)
    {
        if (!$url) {
            $url = urldecode(request()->get('return_url', null));
        }

        $query = '';
        // Making URL query string if parameters given.
        if (!empty($parameters)) {
            $query = '?';

            if (parse_url($url, PHP_URL_QUERY)) {
                $query = '&';
            }

            $query .= http_build_query($parameters);
        }

        app()->abort($httpResponseCode, '', ['Location' => $url . $query]);
    }

    /**
     * Returning json response for the broker.
     *
     * @param null|array $response Response array which will be encoded to json.
     * @param int $httpResponseCode HTTP response code.
     *
     * @return string
     */
    protected function returnJson(?array $response = null, int $httpResponseCode = 200)
    {
        return response()->json($response, $httpResponseCode);
    }

    /**
     * Authenticate using user credentials
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    protected function authenticate(string $username, string $password)
    {
        if (!Auth::attempt(['email' => $username, 'password' => $password])) {
            return false;
        }

        // After authentication Laravel will change session id, but we need to keep
        // this the same because this session id can be already attached to other brokers.
        $sessionId = $this->getBrokerSessionId();
        $savedSessionId = $this->getBrokerSessionData($sessionId);
        $this->startSession($savedSessionId);

        return true;
    }

    /**
     * Get the secret key and other info of a broker
     *
     * @param string $brokerId
     *
     * @return null|array
     */
    protected function getBrokerInfo(string $brokerId)
    {
        try {
            $broker = config('laravel-sso.brokersModel')::where('name', $brokerId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        }

        return $broker;
    }

    /**
     * Get the information about a user
     *
     * @param string $username
     *
     * @return array|object|null
     */
    protected function getUserInfo(string $username)
    {
        try {
            $user = config('laravel-sso.usersModel')::where('email', $username)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        }

        return $user;
    }

    /**
     * Returning user info for broker. Should return json or something like that.
     *
     * @param array|object $user Can be user object or array.
     *
     * @return array|object|UserResource
     */
    protected function returnUserInfo($user)
    {
        return new UserResource($user);
    }

    /**
     * Return session id sent from broker.
     *
     * @return null|string
     */
    protected function getBrokerSessionId()
    {
        $authorization = request()->header('Authorization', null);
        if ($authorization &&  strpos($authorization, 'Bearer') === 0) {
            return substr($authorization, 7);
        }

        return null;
    }

    /**
     * Start new session when user visits server.
     *
     * @return void
     */
    protected function startUserSession()
    {
        // Session must be started by middleware.
    }

    /**
     * Set session data
     *
     * @param string $key
     * @param null|string $value
     *
     * @return void
     */
    protected function setSessionData(string $key, ?string $value = null)
    {
        if (!$value) {
            Session::forget($key);
            return;
        }

        Session::put($key, $value);
    }

    /**
     * Get data saved in session.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getSessionData(string $key)
    {
        if ($key === 'id') {
            return Session::getId();
        }

        return Session::get($key, null);
    }

    /**
     * Start new session with specific session id.
     *
     * @param $sessionId
     *
     * @return void
     */
    protected function startSession(string $sessionId)
    {
        Session::setId($sessionId);
        Session::start();
    }

    /**
     * Save broker session data to cache.
     *
     * @param string $brokerSessionId
     * @param string $sessionData
     *
     * @return void
     */
    protected function saveBrokerSessionData(string $brokerSessionId, string $sessionData)
    {
        Cache::put('broker_session:' . $brokerSessionId, $sessionData, now()->addHour());
    }

    /**
     * Get broker session data from cache.
     *
     * @param string $brokerSessionId
     *
     * @return null|string
     */
    protected function getBrokerSessionData(string $brokerSessionId)
    {
        return Cache::get('broker_session:' . $brokerSessionId);
    }

    /**
     * Check for the User authorization with application and return error or userinfo
     *
     * @return string
     */
    public function checkUserApplicationAuth()
    {
        try {
            if (empty($this->checkBrokerUserAuthentication())) {
                $this->fail('User authorization failed with application.');
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }
        return $this->userInfo();
    }

    /**
     * Returning the broker details
     *
     * @return string
     */
    public function getBrokerDetail()
    {
        return $this->getBrokerInfo($this->brokerId);
    }

    /**
     * Check for User Auth with Broker Application.
     *
     * @return boolean
     */
    protected function checkBrokerUserAuthentication()
    {
        $userInfo = $this->userInfo();
        $broker = $this->getBrokerDetail();
        if (!empty($userInfo->id) && !empty($broker)) {
            $brokerUser = config('laravel-sso.brokersUserModel')::where('user_id', $userInfo->id)->where('broker_id', $broker->id)->first();
            if (empty($brokerUser)) {
                return false;
            }
        }
        return true;
    }
}
