<?php

namespace Zefy\LaravelSSO\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Zefy\LaravelSSO\LaravelSSOServer;

class ServerController extends BaseController
{
    /**
     * @param Request $request
     * @param LaravelSSOServer $server
     *
     * @return void
     */
    public function attach(Request $request, LaravelSSOServer $server)
    {
        $server->attach(
            $request->get('broker', null),
            $request->get('token', null),
            $request->get('checksum', null)
        );
    }

    /**
     * @param Request $request
     * @param LaravelSSOServer $server
     *
     * @return mixed
     */
    public function login(Request $request, LaravelSSOServer $server)
    {
        return $server->login(
            $request->get('username', null),
            $request->get('password', null)
        );
    }

    /**
     * @param LaravelSSOServer $server
     *
     * @return string
     */
    public function logout(LaravelSSOServer $server)
    {
        return $server->logout();
    }

    /**
     * @param LaravelSSOServer $server
     *
     * @return string
     */
    public function userInfo(LaravelSSOServer $server)
    {
        return $server->checkUserApplicationAuth();
    }
}
