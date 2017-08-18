<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.17
 * Time: 15:58
 */

namespace Module;


use Model\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class UserModule
{

    /**
     * Block login action after 3 attempts for a time below
     */
    const LOGIN_ATTEMPT_BLOCKING_TIMEOUT = 300;

    /**
     * Returns prepared password
     *
     * @param $password
     * @return string
     */
    public function preparePassword($password) {
        return trim(preg_replace('/[^-A-Za-z0-9\.@!#$%&()]/', '', $password));
    }

    /**
     * Returns prepared login
     *
     * @param $login
     * @return string
     */
    public function prepareLogin($login) {
        return trim(strtolower(htmlentities($login, ENT_QUOTES)));
    }

    /**
     * Save user login info
     *
     * @param Application\ $app
     * @param Request $request
     * @param $info
     */
    public function saveLoginInfo($app = null, $request = null, $info) {

        $app['session']->set(User::USER_AUTH_KEY, $info);
        $app['session']->set(User::USER_ID_KEY, $info);

    }

    /**
     * Comparing two passwords
     *
     * @param $password1
     * @param $password2
     * @return bool
     */
    public function passwordsAreEquals($password1, $password2) {
        return $password1 === $password2;
    }

    /**
     * Insert user into Database
     *
     * @param $app
     * @param $login
     * @param $password
     * @return bool
     */
    public function insertUser($app, $login, $password) {

        $salt = hash('sha256', $login.$password);
        $user = new User([
            '_id' => $salt,
            '_login' => $login,
            '_password' => hash('sha256', $password),
            '_salt' => $salt
        ]);
        $app['em_user']->persist($user);

        if(!$app['em_user']->flush() != false) {
            $app['session']->getFlashBag()->add('registration_error', 'Something went wrong');

            return false;
        }

        return $user->getSalt();

    }

    /**
     * Block bruteforce
     *
     * @param Application $app
     * @param Request $request
     */
    public function blockBruteforceLogin(Application $app, Request $request): void
    {
        $loginAttempts = $request->getSession()->get('attempts', 0);
        $loginAttempts++;
        if ($loginAttempts < 3) {
            $request->getSession()->set('attempts', $loginAttempts);
        } else {
            $timeout = $request->getSession()->get('timeout', null);
            if (empty($timeout)) {
                $request->getSession()->set('timeout', time() + self::LOGIN_ATTEMPT_BLOCKING_TIMEOUT);
            } else if ($timeout - time() < 0) {
                $request->getSession()->remove('timeout');
                $request->getSession()->remove('attempts');
            }

            if ($request->getSession()->has('timeout')) {
                $app['session']->getFlashBag()->add('login_error', 'Blocked for ' . ($request->getSession()->get('timeout', 0) - time()) . ' sec.');
            }
        }
    }

}