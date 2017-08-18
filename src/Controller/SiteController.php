<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.17
 * Time: 13:05
 */

namespace Controller;


use Extensions\RedirectResponseWithCookies;
use Model\User;
use Module\UserModule;
use Silex\Application;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SiteController
{

    /**
     * @var UserModule
     */
    private $_userModule;

    /**
     * SiteController constructor.
     */
    public function __construct() {
        $this->_userModule = new UserModule();
    }

    /**
     * Index Action
     *
     * @param Application $app
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Application $app, Request $request) {
        if($this->_isAuthorized($app, $request)) {
            return new RedirectResponse(
                $app['url_generator']->generate('profile', ['id' => $app['session']->get(User::USER_ID_KEY)])
            );
        }
        return $app['twig']->render('index.html.twig', [
            'currentRequest' => $request
        ]);
    }

    /**
     * Login Action
     *
     * @param Application $app
     * @param Request $request
     * @return RedirectResponse
     */
    public function login(Application $app, Request $request) {
        if($this->_isAuthorized($app, $request)) {
            return new RedirectResponse(
                $app['url_generator']->generate('profile', ['id' => $app['session']->get(User::USER_ID_KEY)])
            );
        }

        $login = trim(strtolower(htmlentities($request->request->get('_login'), ENT_QUOTES)));
        $password = trim(preg_replace('/[^-A-Za-z0-9\.@!#$%&()]/', '', $request->request->get('_password')));

        /** @var User $user */
        $user = $app['em_user']->findOne(['_login' => $login]);

        if(!empty($user)) {

            if( $this->_userModule->passwordsAreEquals(hash('sha256', $password), $user->getPassword()) ) {

                $this->_userModule->saveLoginInfo($app, null, $user->getSalt());

                $request->getSession()->remove('timeout');
                $request->getSession()->remove('attempts');

                return new RedirectResponse($app['url_generator']->generate('profile', ['id' => $app['session']->get(User::USER_ID_KEY)]));
            } else {
                $this->_userModule->blockBruteforceLogin($app, $request);

                $app['session']->getFlashBag()->add('login_error', 'Bad credentials');
            }

        } else {
            $app['session']->getFlashBag()->add('login_error', 'No user was found with login ' . $login);
        }

        return new RedirectResponse($app['url_generator']->generate('index'));


    }

    /**
     * Registration Action
     *
     * @param Application $app
     * @param Request $request
     * @return RedirectResponse
     */
    public function registration(Application $app, Request $request)
    {

        if ($this->_isAuthorized($app, $request)) {
            return new RedirectResponse(
                $app['url_generator']->generate('profile', ['id' => $app['session']->get(User::USER_ID_KEY)])
            );

        }

        $errors = false;

        if (!empty($request->request->all())) {
            $login = $this->_userModule->prepareLogin($request->request->get('_login'));
            $password = $this->_userModule->preparePassword($request->request->get('_password'));
            $repassword = $this->_userModule->preparePassword($request->request->get('_re-password'));

            $user = $app['em_user']->findOne(['_login' => $login]);

            if(empty($user)) {

                if (!empty($login) && !empty($password) && !empty($repassword)) {

                    if (!$this->_userModule->passwordsAreEquals($password, $repassword)) {
                        $app['session']->getFlashBag()->add('registration_error', 'Passwords are not matched');

                        $errors = true;
                    }

                    if (($salt = $this->_userModule->insertUser($app, $login, $password)) == false) {
                        $errors = true;
                    }

                    if (empty($errors)) {
                        $this->_userModule->saveLoginInfo($app, null, $salt);

                        return new RedirectResponse(
                            $app['url_generator']->generate('profile', ['id' => $app['session']->get(User::USER_ID_KEY)])
                        );
                    }

                    return $app['twig']->render('registration.html.twig', [
                        'currentRequest' => $request
                    ]);

                }

            } else {
                $app['session']->getFlashBag()->add('registration_error', 'User with login ' . $login . ' already exist');
            }

        }

        return $app['twig']->render('registration.html.twig', [
            'currentRequest' => $request
        ]);
    }

    /**
     * User profile view
     *
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function profile(Application $app, Request $request, $id) {
        if($this->_isAuthorized($app, $request) && $id === $app['session']->get(User::USER_AUTH_KEY)) {
            $user = $app['em_user']->find($id);

            if(!empty($user)) {
                return $app['twig']->render('profile.html.twig', [
                    'user' => $user
                ]);
            }

            throw new NotFoundHttpException('User not found');
        }

        throw new AccessDeniedException('Access for user is denied');

    }

    /**
     * Logout action
     *
     * @param Application $app
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Application $app, Request $request) {
        $app['session']->remove(User::USER_AUTH_KEY);
        $app['session']->remove(User::USER_ID_KEY);
        $request->cookies->remove(User::USER_AUTH_KEY);

        return new RedirectResponse($app['url_generator']->generate('index'));
    }

    /**
     * Check user auth data
     *
     * @param Application $app
     * @param Request $request
     * @return bool
     */
    private function _isAuthorized(Application $app, Request $request) {
        return $app['session']->get(User::USER_AUTH_KEY, false) || $request->cookies->get(User::USER_AUTH_KEY, false);
    }
}