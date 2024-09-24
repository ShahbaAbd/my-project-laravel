<?php

namespace PHPageBuilder\Modules\WebsiteManager;

use PHPageBuilder\Contracts\PageContract;
use PHPageBuilder\Contracts\WebsiteManagerContract;
use PHPageBuilder\Extensions;
use PHPageBuilder\Repositories\PageRepository;
use PHPageBuilder\Repositories\SettingRepository;

class WebsiteManager implements WebsiteManagerContract
{
    /**
     * Process the current GET or POST request and redirect or render the requested page.
     *
     * @param $route
     * @param $action
     */
    public function handleRequest($route, $action)
    {
        // session_start();
        if (is_null($route)) {
            $this->renderOverview();
            exit();
        }

        if ($route === 'settings') {
            if ($action === 'renderBlockThumbs') {
                $this->renderBlockThumbs();
                exit();
            }
            if ($action === 'update') {
                $this->handleUpdateSettings();
                exit();
            }
        }

        if ($route === 'page_settings') {
            if ($action === 'create') {
                $this->handleCreate();
                exit();
            }

            $pageId = $_GET['page'] ?? null;
            $pageRepository = new PageRepository;
            $page = $pageRepository->findWithId($pageId);
            if (! ($page instanceof PageContract)) {
                phpb_redirect(phpb_url('website_manager'));
            }

            if ($action === 'edit') {
                $this->handleEdit($page);
                exit();
            } elseif ($action === 'destroy') {
                $this->handleDestroy($page);
            }
        }

        // if ($route === 'auth') {
        //     if ($action === 'login') {
        //         $this->handleLogin();
        //         exit();
        //     }
        // }

    }
    //  /**
    //  * Generate CSRF token.
    //  *
    //  * @return string
    //  */
    // private function generateCsrfToken()
    // {
    //     $token = bin2hex(random_bytes(32));
    //     $_SESSION['csrf_token'] = $token;
    //     return $token;
    // }
    //  /**
    //  * Validate CSRF token.
    //  *
    //  * @param string $token
    //  * @return bool
    //  */
    // private function validateCsrfToken($token)
    // {
    //     return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    // }
    //  /**
    //  * Handle login requests.
    //  */
    // public function handleLogin()
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         if (!isset($_POST['csrf_token']) || !$this->validateCsrfToken($_POST['csrf_token'])) {
    //             // CSRF token is invalid
    //             phpb_redirect(phpb_url('auth', ['action' => 'login']), [
    //                 'message-type' => 'danger',
    //                 'message' => 'CSRF token validation failed'
    //             ]);
    //             exit();
    //         }
    //         // CSRF token is valid, proceed with login process
    //         $username = $_POST['username'] ?? '';
    //         $password = $_POST['password'] ?? '';
            
    //         // Perform your login logic here
    //         // For example:
    //         if ($this->authenticateUser($username, $password)) {
    //             // Login successful
    //             $_SESSION['user_logged_in'] = true;
    //             phpb_redirect(phpb_url('website_manager'));
    //         } else {
    //             // Login failed
    //             phpb_redirect(phpb_url('auth', ['action' => 'login']), [
    //                 'message-type' => 'danger',
    //                 'message' => 'Invalid username or password'
    //             ]);
    //         }
    //     } else {
    //         // GET request, render login form
    //         $this->renderLoginForm();
    //     }
    // }
    //  /**
    //  * Render the login form.
    //  */
    // public function renderLoginForm()
    // {
    //     $csrf_token = $this->generateCsrfToken();
    //     $viewFile = 'login-form';
    //     require __DIR__ . '/../Auth/resources/views/login-form.php';
    // }
    // /**
    //  * Authenticate user (placeholder method).
    //  *
    //  * @param string $username
    //  * @param string $password
    //  * @return bool
    //  */
    // private function authenticateUser($username, $password)
    // {
    //     // Implement your actual authentication logic here
    //     // This is just a placeholder
    //     return $username === 'admin' && $password === 'password';
    // }
    /**
     * Handle requests for creating a new page.
     */
    public function handleCreate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pageRepository = new PageRepository;
            $page = $pageRepository->create($_POST);
            if ($page) {
                phpb_redirect(phpb_url('website_manager'), [
                    'message-type' => 'success',
                    'message' => phpb_trans('website-manager.page-created')
                ]);
            }
        }

        $this->renderPageSettings();
    }

    /**
     * Handle requests for editing the given page.
     *
     * @param PageContract $page
     */
    public function handleEdit(PageContract $page)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pageRepository = new PageRepository;
            $success = $pageRepository->update($page, $_POST);
            if ($success) {
                phpb_redirect(phpb_url('website_manager'), [
                    'message-type' => 'success',
                    'message' => phpb_trans('website-manager.page-updated')
                ]);
            }
        }

        $this->renderPageSettings($page);
    }

    /**
     * Handle requests to destroy the given page.
     *
     * @param PageContract $page
     */
    public function handleDestroy(PageContract $page)
    {
        $pageRepository = new PageRepository;
        $pageRepository->destroy($page->getId());
        phpb_redirect(phpb_url('website_manager'), [
            'message-type' => 'success',
            'message' => phpb_trans('website-manager.page-deleted')
        ]);
    }

    /**
     * Handle requests for updating the website settings.
     */
    public function handleUpdateSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settingRepository = new SettingRepository;
            $success = $settingRepository->updateSettings($_POST);
            if ($success) {
                phpb_redirect(phpb_url('website_manager', ['tab' => 'settings']), [
                    'message-type' => 'success',
                    'message' => phpb_trans('website-manager.settings-updated')
                ]);
            }
        }
    }

    /**
     * Render the website manager overview page.
     */
    public function renderOverview()
    {
        $pageRepository = new PageRepository;
        $pages = $pageRepository->getAll();

        $viewFile = 'overview';
        require __DIR__ . '/resources/layouts/master.php';
    }

    /**
     * Render the website manager page settings (add/edit page form).
     *
     * @param PageContract $page
     */
    public function renderPageSettings(PageContract $page = null)
    {
        $action = isset($page) ? 'edit' : 'create';
        $theme = phpb_instance('theme', [
            phpb_config('theme'), 
            phpb_config('theme.active_theme')
        ]);

        $viewFile = 'page-settings';
        require __DIR__ . '/resources/layouts/master.php';
    }

    /**
     * Render the website manager menu settings (add/edit menu form).
     */
    public function renderMenuSettings()
    {
        $viewFile = 'menu-settings';
        require __DIR__ . '/resources/layouts/master.php';
    }

    /**
     * Render a thumbnail for each theme block.
     */
    public function renderBlockThumbs()
    {
        $viewFile = 'block-thumbs';
        require __DIR__ . '/resources/layouts/master.php';
    }

    /**
     * Render the website manager welcome page for installations without a homepage.
     */
    public function renderWelcomePage()
    {
        $viewFile = 'welcome';
        require __DIR__ . '/resources/layouts/empty.php';
    }
}
